jQuery(document).ready(function ($) {
  /* --------------------
     TOUCH SUPPORT FOR MOBILE
-------------------- */
  //jQuery UI Touch Punch for mobile support
  !(function (a) {
    function f(a, b) {
      if (!(a.originalEvent.touches.length > 1)) {
        a.preventDefault();
        var c = a.originalEvent.changedTouches[0],
          d = document.createEvent("MouseEvents");
        d.initMouseEvent(
          b,
          !0,
          !0,
          window,
          1,
          c.screenX,
          c.screenY,
          c.clientX,
          c.clientY,
          !1,
          !1,
          !1,
          !1,
          0,
          null
        ),
          a.target.dispatchEvent(d);
      }
    }
    if (((a.support.touch = "ontouchend" in document), a.support.touch)) {
      var e,
        b = a.ui.mouse.prototype,
        c = b._mouseInit,
        d = b._mouseDestroy;
      (b._touchStart = function (a) {
        var b = this;
        !e &&
          b._mouseCapture(a.originalEvent.changedTouches[0]) &&
          ((e = !0),
          (b._touchMoved = !1),
          f(a, "mouseover"),
          f(a, "mousemove"),
          f(a, "mousedown"));
      }),
        (b._touchMove = function (a) {
          e && ((this._touchMoved = !0), f(a, "mousemove"));
        }),
        (b._touchEnd = function (a) {
          e &&
            (f(a, "mouseup"),
            f(a, "mouseout"),
            this._touchMoved || f(a, "click"),
            (e = !1));
        }),
        (b._mouseInit = function () {
          var b = this;
          b.element.bind({
            touchstart: a.proxy(b, "_touchStart"),
            touchmove: a.proxy(b, "_touchMove"),
            touchend: a.proxy(b, "_touchEnd"),
          }),
            c.call(b);
        }),
        (b._mouseDestroy = function () {
          var b = this;
          b.element.unbind({
            touchstart: a.proxy(b, "_touchStart"),
            touchmove: a.proxy(b, "_touchMove"),
            touchend: a.proxy(b, "_touchEnd"),
          }),
            d.call(b);
        });
    }
  })(jQuery);

  // --------------------
  // MAP SETUP (Leaflet)
  // --------------------
  let map = L.map("qiog-map").setView([19.3133, -81.2546], 12); // Cayman center
  let markers = {}; // stopId => marker

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "© OpenStreetMap contributors",
  }).addTo(map);

  /* --------------------
     BASE CONFIG WITH CUSTOMIZATION SUPPORT
-------------------- */
  let maxStops = window.qiogConfig?.maxStops || 3;
  let basePrice = window.qiogConfig?.basePriceFor3 || 900;
  let basePriceFor3Stops = window.qiogConfig?.basePriceFor3 || 900;
  let basePriceFor4Stops = window.qiogConfig?.basePriceFor4 || 1100;
  let basePriceFor4StopsPackage = 1050; // ADD THIS LINE - Package 4-stop pricing
  let addonTotal = 0;
  let upgraded = false;
  let isPackageWith4Stops = false; // ADD THIS LINE - Track if current selection is from a 4-stop package
  let pickupMarker = null;

  /* --------------------
     RENDER FUNCTIONS
-------------------- */
  function renderStops(stops) {
    $(".available-stops").empty();

    stops.forEach((stop) => {
      $(".available-stops").append(`
      <div class="qiog-item stop-item"
           data-id="${stop.id}"
           data-image="${stop.image}"
           data-name="${stop.name}"
           data-lat="${stop.lat}"
           data-lng="${stop.lng}"
           data-description="${stop.description || ""}">
        <strong>${stop.name}</strong>
        (${stop.duration} min)
      </div>
    `);
    });
  }

  function renderAddons(addons) {
    $(".available-addons").empty();
    addons.forEach((addon) => {
      $(".available-addons").append(`
            <div class="qiog-item addon-item" 
                 data-id="${addon.id}" 
                 data-price="${addon.price}" 
                 >

                <div class="addon-name">
                    ${addon.name} ($${addon.price})
                </div>

            </div>
        `);
    });
  }

  // Helper: add qty controls to an addon element (used when item is moved to selected-addons)
  function addQtyControls($item) {
    if ($item.find(".addon-qty").length) return;
    var $controls = $(
      '<div class="addon-qty">Qty: <button class="qty-minus">−</button> <span class="qty-value">1</span> <button class="qty-plus">+</button></div>'
    );
    $item.append($controls);
    $item.attr("data-qty", 1);
  }

  // Helper: remove qty controls from an addon element (used when item is moved back to available)
  function removeQtyControls($item) {
    $item.find(".addon-qty").remove();
    $item.removeAttr("data-qty");
  }

  /* --------------------
     FETCH DATA VIA AJAX
-------------------- */
  function fetchStopsAndAddons() {
    $.post(qiogCharter.ajax_url, { action: "qiog_get_stops" }, function (res) {
      if (res.success) renderStops(res.data);
    });

    $.post(qiogCharter.ajax_url, { action: "qiog_get_addons" }, function (res) {
      if (res.success) renderAddons(res.data);
    });
    // Fetch packages
    $.post(
      qiogCharter.ajax_url,
      { action: "qiog_get_packages" },
      function (res) {
        if (res.success) renderPackages(res.data);
      }
    );
  }

  fetchStopsAndAddons();

  /* --------------------
     EMPTY STATE MANAGEMENT
-------------------- */
  function updateEmptyStates() {
    if ($(".selected-stops .stop-item").length > 0) {
      $(".selected-stops .qiog-empty-state").hide();
    } else {
      $(".selected-stops .qiog-empty-state").show();
    }

    if ($(".selected-addons .addon-item").length > 0) {
      $(".selected-addons .qiog-empty-state").hide();
    } else {
      $(".selected-addons .qiog-empty-state").show();
    }

    // Toggle Clear buttons visibility depending on whether there are selected items
    if ($(".selected-stops .stop-item").length > 0) {
      $("#qiog-clear-stops").show();
    } else {
      $("#qiog-clear-stops").hide();
    }

    if ($(".selected-addons .addon-item").length > 0) {
      $("#qiog-clear-addons").show();
    } else {
      $("#qiog-clear-addons").hide();
    }
  }

  /* --------------------
     PACKAGES: render and apply
  -------------------- */
  function renderPackages(packages) {
    $(".available-packages").empty();
    packages.forEach((pkg) => {
      // Build stops list HTML
      let stopsListHtml = "";
      if (pkg.stop_names && pkg.stop_names.length > 0) {
        stopsListHtml = '<ul class="pkg-stop-list">';
        pkg.stop_names.forEach((stopName) => {
          stopsListHtml += `<li>${stopName}</li>`;
        });
        stopsListHtml += "</ul>";
      }

      $(".available-packages").append(`
          <div class="package-card" data-id="${
            pkg.id
          }" data-stops='${JSON.stringify(
        pkg.stops
      )}' data-addons='${JSON.stringify(pkg.addons)}'>
              <div class="pkg-name">${pkg.name}</div>
              ${stopsListHtml}
              ${
                pkg.description
                  ? `<div class="pkg-meta">${pkg.description}</div>`
                  : ""
              }
          </div>
      `);
    });

    // click to apply package
    $(document)
      .off("click", ".package-card")
      .on("click", ".package-card", function () {
        // visually mark selected package
        $(".package-card").removeClass("selected");
        $(this).addClass("selected");
        applyPackage($(this));
      });
  }

  function applyPackage($card) {
    // 🔥 RESET MAP FIRST
    clearAllStopMarkers();
    const packageName = $card.find(".pkg-name").text().trim();
    let stops = JSON.parse($card.attr("data-stops") || "[]");
    let addons = JSON.parse($card.attr("data-addons") || "[]");

    // ADD THIS SECTION - Check if package has 4 stops
    if (stops.length === 4) {
      isPackageWith4Stops = true;
      maxStops = 4; // Allow 4 stops
    } else {
      isPackageWith4Stops = false;
    }

    // Reset current selections: move selected stops/addons back to available
    $(".selected-stops .stop-item").each(function () {
      $(".available-stops").append($(this));
    });
    $(".selected-addons .addon-item").each(function () {
      var $it = $(this);
      removeQtyControls($it);
      $(".available-addons").append($it);
    });

    // Move stops (respect maxStops)
    let currentCount = $(".selected-stops .stop-item").length;
    for (let i = 0; i < stops.length; i++) {
      if (currentCount >= maxStops) break;
      let stopId = stops[i];
      // item may be in available-stops now
      let $item = $(`.available-stops .stop-item[data-id="${stopId}"]`);
      if ($item.length) {
        $(".selected-stops").append($item);
        currentCount++;
        addStopMarker($item);
      }
    }

    // Move addons
    addons.forEach((addonId) => {
      let $a = $(`.available-addons .addon-item[data-id="${addonId}"]`);
      if ($a.length) {
        $(".selected-addons").append($a);
        addQtyControls($a);
      }
    });

    calculateAddonTotal();
    updateSummary();
  }

  /* --------------------
     UPDATE SUMMARY
-------------------- */
  function updateSummary() {
    let selectedStops = $(".selected-stops .stop-item").length;

    // Dynamic pricing based on stops and source (package vs manual)
    if (selectedStops === 4) {
      if (isPackageWith4Stops) {
        // Package with 4 stops: $900 + $150 = $1050
        basePrice = basePriceFor4StopsPackage;
      } else {
        // Manual upgrade: $900 + $200 = $1100
        basePrice = basePriceFor4Stops;
      }
    } else {
      basePrice = basePriceFor3Stops;
      // Reset package flag if stops reduced below 4
      if (selectedStops < 4) {
        isPackageWith4Stops = false;
      }
    }

    $("#qiog-stop-count").text(selectedStops);
    $("#qiog-base-price").text(basePrice);
    $("#qiog-addon-total").text(addonTotal);
    $("#qiog-grand-total").text(basePrice + addonTotal);

    // Upgrade CTA logic
    if (selectedStops === maxStops && !upgraded && maxStops < 4) {
      $("#qiog-upgrade-wrapper").show();
    } else {
      $("#qiog-upgrade-wrapper").hide();
    }

    updateEmptyStates();
  }

  /* --------------------
     CALCULATE ADDON TOTAL
-------------------- */
  function calculateAddonTotal() {
    addonTotal = 0;

    $(".selected-addons .addon-item").each(function () {
      let price = parseInt($(this).data("price"));
      let qty = parseInt($(this).attr("data-qty"));

      addonTotal += price * qty;
    });

    updateSummary();
  }

  /* --------------------
   DRAG & DROP - STOPS (WITH MOBILE SUPPORT)
-------------------- */
  $(".available-stops, .selected-stops").sortable({
    connectWith: ".qiog-stops",
    placeholder: "qiog-placeholder",
    tolerance: "pointer",
    cursor: "move",
    scroll: true,
    scrollSensitivity: 100,
    scrollSpeed: 20,
    delay: 150,
    distance: 10,

    receive: function (event, ui) {
      if ($(this).hasClass("selected-stops")) {
        let selectedCount = $(".selected-stops .stop-item").length;

        if (selectedCount > maxStops) {
          alert("You can only select " + maxStops + " stops.");
          $(ui.sender).sortable("cancel");
          return;
        }

        // ✅ ADD MARKER when manually added
        addStopMarker(ui.item);
      }

      // Unselect package on manual change
      $(".package-card").removeClass("selected");
      isPackageWith4Stops = false;
      updateSummary();
    },

    remove: function (event, ui) {
      // ✅ REMOVE MARKER when stop is removed
      removeStopMarker(ui.item);

      $(".package-card").removeClass("selected");
      isPackageWith4Stops = false;
      updateSummary();
    },

    start: function (event, ui) {
      ui.item.addClass("dragging");
    },

    stop: function (event, ui) {
      ui.item.removeClass("dragging");
    },
  });

  /* --------------------
   UPGRADE STOPS
-------------------- */
  $("#qiog-upgrade-btn").on("click", function () {
    let confirmUpgrade = confirm(
      "Upgrade to 4 stops? Base price will increase to $" +
        basePriceFor4Stops +
        " when you add the 4th stop."
    );

    if (!confirmUpgrade) return;

    upgraded = true;
    maxStops = 4;

    updateSummary();
  });

  /* --------------------
   DRAG & DROP - ADDONS (WITH MOBILE SUPPORT)
-------------------- */
  $(".available-addons, .selected-addons").sortable({
    connectWith: ".qiog-addons",
    placeholder: "qiog-placeholder",
    tolerance: "pointer",
    cursor: "move",
    scroll: true,
    scrollSensitivity: 100,
    scrollSpeed: 20,
    delay: 150,
    distance: 10,
    receive: function (event, ui) {
      var $item = $(ui.item);
      if ($(this).hasClass("selected-addons")) {
        // moved into selected list -> ensure qty controls
        addQtyControls($item);
      } else if ($(this).hasClass("available-addons")) {
        // moved back to available -> remove qty controls
        removeQtyControls($item);
      }
      calculateAddonTotal();
    },
    remove: function () {
      calculateAddonTotal();
    },
    start: function (event, ui) {
      ui.item.addClass("dragging");
    },
    stop: function (event, ui) {
      ui.item.removeClass("dragging");
    },
  });

  /* --------------------
   ADDON QUANTITY LOGIC
-------------------- */
  $(document).on("click", ".qty-plus", function (e) {
    e.stopPropagation(); // Prevent drag on button click
    let addon = $(this).closest(".addon-item");
    let qty = parseInt(addon.attr("data-qty"));

    qty++;
    addon.attr("data-qty", qty);
    addon.find(".qty-value").text(qty);

    calculateAddonTotal();
  });

  $(document).on("click", ".qty-minus", function (e) {
    e.stopPropagation(); // Prevent drag on button click
    let addon = $(this).closest(".addon-item");
    let qty = parseInt(addon.attr("data-qty"));

    if (qty <= 1) return;

    qty--;
    addon.attr("data-qty", qty);
    addon.find(".qty-value").text(qty);

    calculateAddonTotal();
  });

  /* --------------------
   MAP MARKER LOGIC
-------------------- */

  function addStopMarker($stopEl) {
    const id = $stopEl.data("id");
    if (markers[id]) return;

    const name = $stopEl.data("name");
    const imageUrl = $stopEl.data("image") || "https://via.placeholder.com/150"; // fallback
    const lat = parseFloat($stopEl.data("lat"));
    const lng = parseFloat($stopEl.data("lng"));
    const description = $stopEl.data("description");

    if (!lat || !lng) return;

    const popupContent = `
        <div style="width: 100px; font-family: Arial, sans-serif; text-align: center;">
            <div style="height: 50px; overflow: hidden; margin-bottom: 8px;">
                <img src="${imageUrl}" alt="${name}" style="width: 100%; object-fit: cover;">
            </div>
            <strong style="display: block; margin-bottom: 4px; font-size: 15px;">${name}</strong>
            <p style="margin: 0; font-size: 12px; color: #555;">${
              description ? description : ""
            }</p>
        </div>
    `;

    const marker = L.marker([lat, lng]).addTo(map);
    marker.bindPopup(popupContent, {
      autoClose: false,
      closeOnClick: false,
      closeButton: false,
    });
    // marker.openPopup();
    markers[id] = marker;
    centerMapToMarkers();
  }

  function clearAllStopMarkers() {
    Object.keys(markers).forEach((id) => {
      map.removeLayer(markers[id]);
      delete markers[id];
    });
  }

  function removeStopMarker($stopEl) {
    const id = $stopEl.data("id");

    if (markers[id]) {
      map.removeLayer(markers[id]);
      delete markers[id];
    }
  }

  function centerMapToMarkers() {
    const markerList = Object.values(markers);
    if (!markerList.length) return;

    const group = L.featureGroup(markerList);
    map.fitBounds(group.getBounds(), {
      padding: [40, 40],
      animate: true,
      maxZoom: 12,
    });
  }

  // --------------------
  // PICKUP LOCATION BUTTON
  // --------------------
  $("#qiog-pickup-btn").on("click", function () {
    const lat = parseFloat(qiogCharter.pickup_lat);
    const lng = parseFloat(qiogCharter.pickup_lng);
    const label = qiogCharter.pickup_label || "Pickup Location";

    if (!lat || !lng) {
      alert("Pickup location not set.");
      return;
    }

    // Remove old pickup marker if exists
    if (pickupMarker) {
      map.removeLayer(pickupMarker);
    }

    pickupMarker = L.marker([lat, lng], {
      draggable: false,
    }).addTo(map);

    pickupMarker
      .bindPopup(`<strong>${label}</strong>`, {
        autoClose: false,
        closeOnClick: false,
      })
      .openPopup();

    // Center map on pickup
    map.setView([lat, lng], 13);
  });

  /* --------------------
   CHECKOUT LOGIC
-------------------- */
  $("#qiog-checkout-btn").on("click", function () {
    let stops = [];
    $(".selected-stops .stop-item").each(function () {
      stops.push($(this).text().trim());
    });

    let addons = [];
    $(".selected-addons .addon-item").each(function () {
      addons.push({
        name: $(this).find(".addon-name").text().trim(),
        qty: $(this).attr("data-qty"),
        price: $(this).data("price"),
      });
    });

    // Check if a package is selected
    const selectedPackage = $(".package-card.selected");
    const packageName =
      selectedPackage.length > 0
        ? selectedPackage.find(".pkg-name").text().trim()
        : null;

    let bookingData = {
      package_name: packageName,
      stops: stops,
      addons: addons,
      stops_count: $(".selected-stops .stop-item").length,
      base_price: basePrice,
      addon_total: addonTotal,
      grand_total: basePrice + addonTotal,
    };

    sessionStorage.setItem("qiog_booking", JSON.stringify(bookingData));
    window.location.href = qiogCharter.checkout_url;
  });

  // Initialize empty states on load
  updateEmptyStates();

  // Clear buttons
  $(document).on("click", "#qiog-clear-stops", function (e) {
    e.preventDefault();
    // 🔥 Clear map completely
    clearAllStopMarkers();

    // Move all selected stops back to available
    $(".selected-stops .stop-item").each(function () {
      $(".available-stops").append($(this));
    });

    // Unselect any selected package
    $(".package-card").removeClass("selected");
    isPackageWith4Stops = false;
    updateSummary();
  });

  $(document).on("click", "#qiog-clear-addons", function (e) {
    e.preventDefault();
    // Move all selected addons back to available and remove qty controls
    $(".selected-addons .addon-item").each(function () {
      var $it = $(this);
      removeQtyControls($it);
      $(".available-addons").append($it);
    });
    calculateAddonTotal();
  });

  /* --------------------
      LOAD BOOKING ON CHECKOUT PAGE
-------------------- */
  $(document).ready(function () {
    let bookingData = sessionStorage.getItem("qiog_booking");
    if (bookingData) {
      bookingData = JSON.parse(bookingData);

      let html = "<h4>Stops (" + bookingData.stops_count + ")</h4><ul>";
      bookingData.stops.forEach((stop) => {
        html += "<li>" + stop + "</li>";
      });
      html += "</ul>";

      html += "<h4>Add-ons</h4>";
      if (bookingData.addons.length > 0) {
        html += "<ul>";
        bookingData.addons.forEach((addon) => {
          html +=
            "<li>" +
            addon.name +
            " × " +
            addon.qty +
            " ($" +
            addon.price +
            " each)</li>";
        });
        html += "</ul>";
      } else {
        html += "<p>No add-ons selected.</p>";
      }

      html +=
        "<p><strong>Base Price:</strong> $" + bookingData.base_price + "</p>";
      html +=
        "<p><strong>Add-ons Total:</strong> $" +
        bookingData.addon_total +
        "</p>";
      html +=
        "<p><strong>Grand Total:</strong> $" + bookingData.grand_total + "</p>";

      $("#qiog-booking-summary").html(html);
    } else {
      $("#qiog-booking-summary").html(
        "<p>No booking data found. Please build your charter first.</p>"
      );
      $("#qiog-customer-form").hide();
    }
  });

  /* --------------------
      CUSTOMER FORM SUBMISSION
-------------------- */
  $(document).on("submit", "#qiog-customer-form", function (e) {
    e.preventDefault();

    let bookingData = sessionStorage.getItem("qiog_booking");
    if (!bookingData) {
      alert("Booking data missing. Please build your charter first.");
      return;
    }
    bookingData = JSON.parse(bookingData);

    // Serialize form + add booking data + action
    let formData = $(this).serializeArray();
    formData.push(
      { name: "booking", value: JSON.stringify(bookingData) },
      { name: "action", value: "qiog_save_customer" } // <-- must be in POST data
    );

    $.post(qiogCharter.ajax_url, formData, function (response) {
      if (response.success) {
        $("#qiog-checkout-message").html(
          "<p>Booking confirmed. We’ll contact you shortly.</p>"
        );
        $("#qiog-customer-form").hide();

        // Clear sessionStorage
        sessionStorage.removeItem("qiog_booking");
      } else {
        alert("Error saving booking. Please try again.");
      }
    });
  });
});
