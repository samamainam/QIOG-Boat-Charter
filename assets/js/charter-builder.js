jQuery(document).ready(function ($) {
  /* --------------------
     BASE CONFIG WITH CUSTOMIZATION SUPPORT
-------------------- */
  let maxStops = window.qiogConfig?.maxStops || 3;
  let basePrice = window.qiogConfig?.basePriceFor3 || 900;
  let basePriceFor3Stops = window.qiogConfig?.basePriceFor3 || 900;
  let basePriceFor4Stops = window.qiogConfig?.basePriceFor4 || 1100;
  let addonTotal = 0;
  let upgraded = false;

  /* --------------------
     RENDER FUNCTIONS
-------------------- */
  function renderStops(stops) {
    $(".available-stops").empty();
    stops.forEach((stop) => {
      $(".available-stops").append(`
            <div class="qiog-item stop-item" data-id="${stop.id}">
                ${stop.name}
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
                 data-qty="1">

                <div class="addon-name">
                    ${addon.name} ($${addon.price})
                </div>

                <div class="addon-qty">
                    Qty:
                    <button class="qty-minus">−</button>
                    <span class="qty-value">1</span>
                    <button class="qty-plus">+</button>
                </div>

            </div>
        `);
    });
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
  }

  /* --------------------
     UPDATE SUMMARY
-------------------- */
  function updateSummary() {
    let selectedStops = $(".selected-stops .stop-item").length;

    // Dynamic pricing based on stops
    if (selectedStops === 4) {
      basePrice = basePriceFor4Stops;
    } else {
      basePrice = basePriceFor3Stops;
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
   DRAG & DROP - STOPS
-------------------- */
  $(".available-stops, .selected-stops")
    .sortable({
      connectWith: ".qiog-stops",
      placeholder: "qiog-placeholder",
      receive: function (event, ui) {
        if ($(this).hasClass("selected-stops")) {
          let selectedCount = $(".selected-stops .stop-item").length;

          if (selectedCount > maxStops) {
            alert("You can only select " + maxStops + " stops.");
            $(ui.sender).sortable("cancel");
            return;
          }
        }

        updateSummary();
      },
      remove: function () {
        updateSummary();
      },
    })
    .disableSelection();

  /* --------------------
   UPGRADE STOPS
-------------------- */
  $("#qiog-upgrade-btn").on("click", function () {
    let confirmUpgrade = confirm(
      "Upgrade to 4 stops? Base price will increase to $" + basePriceFor4Stops + " when you add the 4th stop."
    );

    if (!confirmUpgrade) return;

    upgraded = true;
    maxStops = 4;

    updateSummary();
  });

  /* --------------------
   DRAG & DROP - ADDONS
-------------------- */
  $(".available-addons, .selected-addons")
    .sortable({
      connectWith: ".qiog-addons",
      placeholder: "qiog-placeholder",
      receive: function () {
        calculateAddonTotal();
      },
      remove: function () {
        calculateAddonTotal();
      },
    })
    .disableSelection();

  /* --------------------
   ADDON QUANTITY LOGIC
-------------------- */
  $(document).on("click", ".qty-plus", function () {
    let addon = $(this).closest(".addon-item");
    let qty = parseInt(addon.attr("data-qty"));

    qty++;
    addon.attr("data-qty", qty);
    addon.find(".qty-value").text(qty);

    calculateAddonTotal();
  });

  $(document).on("click", ".qty-minus", function () {
    let addon = $(this).closest(".addon-item");
    let qty = parseInt(addon.attr("data-qty"));

    if (qty <= 1) return;

    qty--;
    addon.attr("data-qty", qty);
    addon.find(".qty-value").text(qty);

    calculateAddonTotal();
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

    let bookingData = {
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
