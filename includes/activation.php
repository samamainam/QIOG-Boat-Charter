<?php
if (!defined('ABSPATH'))
    exit;

function qiog_plugin_activate()
{
    qiog_create_charter_tables();
    qiog_create_required_pages();
}
