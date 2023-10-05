<?php echo $header; ?><?php echo $column_left; ?>

<!--
    Define module config code to be used bellow
    We need that config key equals with input name
-->
<?php $configCode = 'payment_lunar'; ?>

<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="module-config-form" data-toggle="tooltip" title="<?php echo $button_save; ?>"
                        class="btn btn-primary"><i class="fa fa-save"></i></button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>"
                        class="btn btn-default"><i class="fa fa-reply"></i></a>
                <a href="<?php echo $module_payments; ?>" data-toggle="tooltip"
                        title="<?php echo $button_payments; ?>" class="btn btn-success"><i
                            class="fa fa-calculator"></i></a>
            </div>
            <h1><?php echo $heading_title; ?></h1>
            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
        <?php if($error_warning){ ?>
        <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i>&nbsp;<?php echo $error_warning; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } ?>
        <?php if($success){ ?>
        <div class="alert alert-success"><i class="fa fa-exclamation-circle"></i>&nbsp;<?php echo $success; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } ?>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title" data-module-version="<?php echo $text_module_version; ?>"><i class="fa fa-pencil"></i>&nbsp;<?php echo $text_edit_settings; ?></h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="module-config-form"
                        class="form-horizontal">

                    <div class="alert alert-info"><?php echo $text_description; ?></div>


                    <!-- Store select begin -->
                    <div class="col-sm-12 pr-40 required">
                        <label class="col-sm-2 control-label" for="config_selected_store"><span
                                    data-toggle="tooltip"
                                    title="<?php echo $help_select_store; ?>"><?php echo $select_store; ?></span></label>
                        <div class="col-sm-10">
                            <select name="config_selected_store" id="config_selected_store"
                                    class="form-control">
                                <?php foreach ($stores as $store) { ?>
                                        <option value="<?php echo $store['store_id']; ?>"
                                            <?php if($store['store_id'] == $config_selected_store) {
                                                echo 'selected="selected"';
                                            } ?>
                                            >
                                            <?php echo $store['name']; ?>
                                        </option>
                                <?php } ?>
                            </select>
                            <div class="select-store-dropdown-error"></div>
                        </div>
                    </div>
                    <!-- Store select end -->


                    <ul class="nav nav-tabs" id="tabs">
                        <li class="active"><a href="#tab-general_settings"
                                    data-toggle="tab"><?php echo $text_general_settings; ?></a></li>
                        <li><a href="#tab-advanced_settings"
                                    data-toggle="tab"><?php echo $text_advanced_settings; ?></a></li>
                    </ul>

                    <div class="tab-content">

                        <div class="tab-pane active" id="tab-general_settings">

                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="module_status"><span
                                            data-toggle="tooltip"
                                            title="<?php echo $help_payment_enabled; ?>"><?php echo $entry_payment_enabled; ?></span></label>
                                <div class="col-sm-10">
                                    <select name="<?php echo $configCode; ?>_status" id="module_status"
                                            class="form-control">
                                    <?php if($module_status){ ?>
                                        <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                        <option value="0"><?php echo $text_disabled; ?></option>
                                    <?php } else { ?>
                                        <option value="1"><?php echo $text_enabled; ?></option>
                                        <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                    <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group required">
                                <label class="col-sm-2 control-label" for="module_method_title"><span
                                            data-toggle="tooltip"
                                            title="<?php echo $help_payment_method_title; ?>"><?php echo $entry_payment_method_title; ?></span></label>
                                <div class="col-sm-10">
                                    <input type="text" name="<?php echo $configCode; ?>_method_title"
                                            value="<?php echo $module_method_title; ?>"
                                            placeholder="<?php echo $entry_payment_method_title; ?>"
                                            id="module_method_title" class="form-control"/>
                                    <?php if($error_payment_method_title){ ?>
                                    <div class="text-danger"><?php echo $error_payment_method_title; ?></div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><span data-toggle="tooltip"
                                            title="<?php echo $help_checkout_cc_logo; ?>"><?php echo $entry_checkout_cc_logo; ?></span></label>
                                <div class="col-sm-10">
                                    <div class="well well-sm" style="overflow: auto;">
                                        <?php foreach($ccLogos as $ccLogo){ ?>
                                        <div class="checkbox">
                                            <label>
                                                <?php if(is_array($module_checkout_cc_logo) && in_array($ccLogo['logo'], $module_checkout_cc_logo)){ ?>
                                                <input type="checkbox" name="<?php echo $configCode; ?>_checkout_cc_logo[]"
                                                        value="<?php echo $ccLogo['logo']; ?>" checked="checked"/>
                                                <?php echo $ccLogo['name']; ?>
                                                <?php } else { ?>
                                                <input type="checkbox" name="<?php echo $configCode; ?>_checkout_cc_logo[]"
                                                        value="<?php echo $ccLogo['logo']; ?>"/>
                                                <?php echo $ccLogo['name']; ?>
                                                <?php } ?>
                                            </label>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="module_popup_title"><span
                                            data-toggle="tooltip"
                                            title="<?php echo $help_checkout_popup_title; ?>"><?php echo $entry_checkout_popup_title; ?></span></label>
                                <div class="col-sm-10">
                                    <input type="text" name="<?php echo $configCode; ?>_checkout_title"
                                            value="<?php echo $module_checkout_title; ?>"
                                            placeholder="<?php echo $entry_checkout_popup_title; ?>"
                                            id="module_popup_title" class="form-control"/>
                                </div>
                            </div>

                            <!--
                                Show the following fields only in debug mode
                            -->

                            <div class="form-group" <?php if(!$debugMode) echo 'hidden'; ?> >
                                <label class="col-sm-2 control-label" for="module_api_mode"><span data-toggle="tooltip"
                                            title="<?php echo $help_api_mode; ?>"><?php echo $entry_api_mode; ?></span></label>
                                <div class="col-sm-10">
                                    <select name="<?php echo $configCode; ?>_api_mode" id="module_api_mode" class="form-control">
                                        <?php if($module_api_mode == 'test'){ ?>
                                        <option value="test" selected="selected"><?php echo $text_test; ?></option>
                                        <option value="live"><?php echo $text_live; ?></option>
                                        <?php } else { ?>
                                        <option value="test"><?php echo $text_test; ?></option>
                                        <option value="live" selected="selected"><?php echo $text_live; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group required js-test-key" <?php if(!$debugMode) echo 'hidden'; ?> >
                                <label class="col-sm-2 control-label" for="module_app_key_test"><span
                                            data-toggle="tooltip"
                                            title="<?php echo $help_app_key_test; ?>"><?php echo $entry_app_key_test; ?></span></label>
                                <div class="col-sm-10">
                                    <input type="text" name="<?php echo $configCode; ?>_app_key_test"
                                            value="<?php echo $module_app_key_test; ?>"
                                            placeholder="<?php echo $entry_app_key_test; ?>" id="module_app_key_test"
                                            class="form-control"/>
                                    <?php if($error_app_key_test){ ?>
                                    <div class="text-danger"><?php echo $error_app_key_test; ?></div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="form-group required js-test-key" <?php if(!$debugMode) echo 'hidden'; ?> >
                                <label class="col-sm-2 control-label" for="module_public_key_test"><span
                                            data-toggle="tooltip"
                                            title="<?php echo $help_public_key_test; ?>"><?php echo $entry_public_key_test; ?></span></label>
                                <div class="col-sm-10">
                                    <input type="text" name="<?php echo $configCode; ?>_public_key_test"
                                            value="<?php echo $module_public_key_test; ?>"
                                            placeholder="<?php echo $entry_public_key_test; ?>"
                                            id="module_public_key_test" class="form-control"/>
                                    <?php if($error_public_key_test){ ?>
                                    <div class="text-danger"><?php echo $error_public_key_test; ?></div>
                                    <?php } ?>
                                </div>
                            </div>


                            <div class="form-group required js-live-key">
                                <label class="col-sm-2 control-label" for="module_app_key_live"><span
                                            data-toggle="tooltip"
                                            title="<?php echo $help_app_key_live; ?>"><?php echo $entry_app_key_live; ?></span></label>
                                <div class="col-sm-10">
                                    <input type="text" name="<?php echo $configCode; ?>_app_key_live"
                                            value="<?php echo $module_app_key_live; ?>"
                                            placeholder="<?php echo $entry_app_key_live; ?>" id="module_app_key_live"
                                            class="form-control"/>
                                    <?php if($error_app_key_live){ ?>
                                    <div class="text-danger"><?php echo $error_app_key_live; ?></div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="form-group required js-live-key">
                                <label class="col-sm-2 control-label" for="module_public_key_live"><span
                                            data-toggle="tooltip"
                                            title="<?php echo $help_public_key_live; ?>"><?php echo $entry_public_key_live; ?></span></label>
                                <div class="col-sm-10">
                                    <input type="text" name="<?php echo $configCode; ?>_public_key_live"
                                            value="<?php echo $module_public_key_live; ?>"
                                            placeholder="<?php echo $entry_public_key_live; ?>"
                                            id="module_public_key_live" class="form-control"/>
                                    <?php if($error_public_key_live){ ?>
                                    <div class="text-danger"><?php echo $error_public_key_live; ?></div>
                                    <?php } ?>
                                </div>
                            </div>


                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="module_capture_mode"><span
                                            data-toggle="tooltip"
                                            title="<?php echo $help_capture_mode; ?>"><?php echo $entry_capture_mode; ?></span></label>
                                <div class="col-sm-10">
                                    <select name="<?php echo $configCode; ?>_capture_mode" id="module_capture_mode"
                                            class="form-control">
                                        <?php if($module_capture_mode == 'instant'){ ?>
                                        <option value="instant"
                                                selected="selected"><?php echo $text_capture_instant; ?></option>
                                        <option value="delayed"><?php echo $text_capture_delayed; ?></option>
                                        <?php } else { ?>
                                        <option value="instant"><?php echo $text_capture_instant; ?></option>
                                        <option value="delayed"
                                                selected="selected"><?php echo $text_capture_delayed; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>


                        </div>


                        <div class="tab-pane" id="tab-advanced_settings">

                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="module_authorize_status_id"><span
                                            data-toggle="tooltip"
                                            title="<?php echo $help_authorize_status_id; ?>"><?php echo $entry_authorize_status_id; ?></span></label>
                                <div class="col-sm-10">
                                    <select name="<?php echo $configCode; ?>_authorize_status_id" id="module_authorize_status_id"
                                            class="form-control">
                                        <?php foreach($order_statuses as $order_status){ ?>
                                        <?php if($order_status['order_status_id'] == $module_authorize_status_id){ ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"
                                                selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="module_capture_status_id"><span
                                            data-toggle="tooltip"
                                            title="<?php echo $help_capture_status_id; ?>"><?php echo $entry_capture_status_id; ?></span></label>
                                <div class="col-sm-10">
                                    <select name="<?php echo $configCode; ?>_capture_status_id" id="module_capture_status_id"
                                            class="form-control">
                                        <?php foreach($order_statuses as $order_status){ ?>
                                        <?php if($order_status['order_status_id'] == $module_capture_status_id){ ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"
                                                selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="module_refund_status_id"><span
                                            data-toggle="tooltip"
                                            title="<?php echo $help_refund_status_id; ?>"><?php echo $entry_refund_status_id; ?></span></label>
                                <div class="col-sm-10">
                                    <select name="<?php echo $configCode; ?>_refund_status_id" id="module_refund_status_id"
                                            class="form-control">
                                        <?php foreach($order_statuses as $order_status){ ?>
                                        <?php if($order_status['order_status_id'] == $module_refund_status_id){ ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"
                                                selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="module_void_status_id"><span
                                            data-toggle="tooltip"
                                            title="<?php echo $help_void_status_id; ?>"><?php echo $entry_void_status_id; ?></span></label>
                                <div class="col-sm-10">
                                    <select name="<?php echo $configCode; ?>_void_status_id" id="module_void_status_id"
                                            class="form-control">
                                        <?php foreach($order_statuses as $order_status){ ?>
                                        <?php if($order_status['order_status_id'] == $module_void_status_id){ ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"
                                                selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="module_logging"><span data-toggle="tooltip"
                                            title="<?php echo $help_logging; ?>"><?php echo $entry_logging; ?></span></label>
                                <div class="col-sm-10">
                                    <select name="<?php echo $configCode; ?>_logging" id="module_logging" class="form-control">
                                        <?php if($module_logging){ ?>
                                        <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                        <option value="0"><?php echo $text_disabled; ?></option>
                                        <?php } else { ?>
                                        <option value="1"><?php echo $text_enabled; ?></option>
                                        <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="module_minimum_total"><span
                                            data-toggle="tooltip"
                                            title="<?php echo $help_minimum_total; ?>"><?php echo $entry_minimum_total; ?></span></label>
                                <div class="col-sm-10">
                                    <input type="text" name="<?php echo $configCode; ?>_minimum_total"
                                            value="<?php echo $module_minimum_total; ?>"
                                            placeholder="<?php echo $entry_minimum_total; ?>" id="module_minimum_total"
                                            class="form-control"/>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="module_geo_zone"><span data-toggle="tooltip"
                                            title="<?php echo $help_geo_zone; ?>"><?php echo $entry_geo_zone; ?></span></label>
                                <div class="col-sm-10">
                                    <select name="<?php echo $configCode; ?>_geo_zone" id="module_geo_zone" class="form-control">
                                        <option value="0"><?php echo $text_all_zones; ?></option>
                                        <?php foreach($geo_zones as $geo_zone){ ?>
                                        <?php if($geo_zone['geo_zone_id'] == $module_geo_zone){ ?>
                                        <option value="<?php echo $geo_zone['geo_zone_id']; ?>"
                                                selected="selected"><?php echo $geo_zone['name']; ?></option>
                                        <?php } else { ?>
                                        <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="module__sort_order"><span
                                            data-toggle="tooltip"
                                            title="<?php echo $help_sort_order; ?>"><?php echo $entry_sort_order; ?></span></label>
                                <div class="col-sm-10">
                                    <input type="text" name="<?php echo $configCode; ?>_sort_order"
                                            value="<?php echo $module_sort_order; ?>"
                                            placeholder="<?php echo $entry_sort_order; ?>" id="module__sort_order"
                                            class="form-control"/>
                                </div>
                            </div>

                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    $('#tabs a:first').tab('show');

    /** Triggered on selection change */
    $(document).on('change','#module_api_mode', function(){
        toggleFields($(this).val());
    });

    /** Show/Hide live and test fields based on selected value of the "#module_api_mode" selectbox*/
    function toggleFields(mode){
        if(mode == 'live'){
            $('.js-live-key').show();
        }
    }

    /** Hide live fields when test mode active - prevents misuse. */
    if ('debug' !== document.location.search.match(/debug/gi)?.toString()) {
        if ('test' === $("#module_api_mode").val()) {
            $('.js-live-key').hide();
        }
    }

    /**
     * On select a store, populate form data with seleted store config data
     */
    $("#config_selected_store").on("change", function() {

        /** Remove any previous errors. */
        $(".select-store-dropdown-error").val();

        /** Set store ID variable. */
        var storeId = $(this).val();

        /** PAyment module configuration code prefix. */
        const configCode = 'payment_lunar';

        /** Ajax call to get selected store config data. */
        $.ajax({
            method: "POST",
            url: "index.php?route=extension/payment/lunar/get_store_settings&user_token=" + getURLVar("user_token"),
            dataType: "json",
            contentType: "application/x-www-form-urlencoded",
            data: {
                store_id: storeId
            },
            success: function (data, status, xhr) {

                /** Add selected attribute to selected store option */
                setSelectedAttributeOnSelectedOption(this.id, storeId)

                /** Add status option to status input. If not exists, change to disabled. */
                $("#module_status option").each(function () {
                    if (data?.[`${configCode}_status`]) {
                        if (data[`${configCode}_status`] !== $(this).val()) {
                            $(this).removeAttr("selected");
                        } else {
                            $(this).attr("selected", true);
                        }
                    } else {
                        if (1 == $(this).val()) {
                            $(this).removeAttr("selected");
                        } else {
                            $(this).attr("selected", true);
                        }
                    }
                });

                $("#module_method_title").val(data?.[`${configCode}_method_title`] ?? '');
                $("#module_popup_title").val(data?.[`${configCode}_checkout_title`] ?? '');

                $(`input[name='${configCode}_checkout_cc_logo[]']`).each(function () {
                    if (`${configCode}_checkout_cc_logo` in data) {
                        if ( ! data[`${configCode}_checkout_cc_logo`].includes($(this).val())) {
                            $(this).prop("checked", false);
                        } else {
                            $(this).prop("checked", true);
                        }
                    } else {
                        $(this).prop("checked", true);
                    }
                });


                /** Set selected on api mode dropdown. Default = live. */
                setSelectedAttributeOnSelectedOption("module_api_mode", data?.[`${configCode}_api_mode`] ?? "live");
                toggleFields(data?.["module_api_mode"] ?? "live")

                /** Set selected on capture mode dropdown. Default = delayed. */
                setSelectedAttributeOnSelectedOption("module_capture_mode", data?.[`${configCode}_capture_mode`] ?? "delayed");

                $("#module_app_key_test").val(data?.[`${configCode}_app_key_test`] ?? '');
                $("#module_public_key_test").val(data?.[`${configCode}_public_key_test`] ?? '');
                $("#module_app_key_live").val(data?.[`${configCode}_app_key_live`] ?? '');
                $("#module_public_key_live").val(data?.[`${configCode}_public_key_live`] ?? '');

                /**
                 * !!! HARDCODED ORDER STATUSES IDS !!!
                 */
                $("#module_authorize_status_id").val(data?.[`${configCode}_authorize_status_id`] ?? 1);
                $("#module_capture_status_id").val(data?.[`${configCode}_capture_status_id`] ?? 5);
                $("#module_refund_status_id").val(data?.[`${configCode}_refund_status_id`] ?? 11);
                $("#module_void_status_id").val(data?.[`${configCode}_void_status_id`] ?? 16);

                $("#module_logging").val(data?.[`${configCode}_logging`] ?? 0);
                $("#module_minimum_total").val(data?.[`${configCode}_minimum_total`] ?? 0);
                $("#module_geo_zone").val(data?.[`${configCode}_geo_zone`] ?? 0);
                $("#module_sort_order").val(data?.[`${configCode}_sort_order`] ?? 0);

            },
            error: function (jqXhr, textStatus, errorMessage) {
                /** Check if error is of type "parsererror". This shows up when token expire. */
                if ('parsererror' == textStatus) {
                    location.href = 'index.php?route=extension/payment/lunar';
                } else {
                    $(".select-store-dropdown-error").append("Error: " + errorMessage).addClass("text-danger");
                    console.error(errorMessage)
                }
            }
        });

    });


    /** Module status set "selected" on change. */
    $("#module_status").on("change", function() {
        setSelectedAttributeOnSelectedOption(this.id, $(this).val());
    });

    /** Module api mode set "selected" on change. */
    $("#module_api_mode").on("change", function() {
        setSelectedAttributeOnSelectedOption(this.id, $(this).val());
    });

    /**
     * Function "Set Selected Attribute On Selected Option"
     * Set selected attribute on dropdown selected option
     */
    function setSelectedAttributeOnSelectedOption(selector, value) {
        $("#" + selector + " option").each(function () {
            if (value !== $(this).val()) {
                $(this).removeAttr("selected");
            } else {
                $(this).attr("selected", true);
            }
        });
    }

</script>

<?php echo $footer; ?>
