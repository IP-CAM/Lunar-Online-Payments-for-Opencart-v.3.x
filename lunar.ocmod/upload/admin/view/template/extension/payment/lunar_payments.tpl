<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">

    <div class="page-header">
        <div class="container-fluid">
            <h1><?php echo $heading_title; ?></h1>
            <ul class="breadcrumb">
                <?php foreach($breadcrumbs as $breadcrumb){ ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
        <?php if($error_warning){ ?>
        <div class="alert alert-danger alert-dismissible"><i
                    class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } ?>
        <div class="row">
            <div id="filter-order" class="col-md-3 col-md-push-9 col-sm-12 hidden-sm hidden-xs">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-filter"></i> <?php echo $text_filter; ?></h3>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label class="control-label" for="input-order-id"><?php echo $column_order_id; ?></label>
                            <input type="text" name="filter_order_id" value="<?php echo $filter_order_id; ?>"
                                    placeholder="<?php echo $column_order_id; ?>" id="input_order_id"
                                    class="form-control"/>
                        </div>

                        <div class="form-group">
                            <label class="control-label"
                                    for="input-customer"><?php echo $column_transaction_id; ?></label>
                            <input type="text" name="filter_customer" value="<?php echo $filter_transaction_id; ?>"
                                    placeholder="<?php echo $column_transaction_id; ?>" id="input_transaction_id"
                                    class="form-control"/>
                        </div>

                        <div class="form-group">
                            <label class="control-label"
                                    for="input_transaction_type"><?php echo $entry_transaction_type; ?></label>
                            <select name="filter_transaction_type" id="input_transaction_type" class="form-control">
                                <option value=""></option>
                                <?php foreach($transaction_types as $type){ ?>
                                <?php if($type == $filter_transaction_type){ ?>
                                <option value="<?php echo $type; ?>" selected="selected"><?php echo $type; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                                <?php } ?>
                                <?php } ?>

                            </select>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="input_date_added"><?php echo $entry_date_added; ?></label>
                            <div class="input-group date">
                                <input type="text" name="filter_date_added" value="<?php echo $filter_date_added; ?>"
                                        placeholder="<?php echo $entry_date_added; ?>" data-date-format="YYYY-MM-DD"
                                        id="input_date_added" class="form-control"/>
                                <span class="input-group-btn">
                                <button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
                            </span>
                            </div>
                        </div>
                        <div class="form-group text-right">
                            <button type="button" id="button-filter" class="btn btn-default"><i
                                        class="fa fa-filter"></i> <?php echo $button_filter; ?></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-9 col-md-pull-3 col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $text_list; ?></h3>
                    </div>
                    <div class="panel-body">
                        <form method="post" action="" enctype="multipart/form-data" id="form-order">
                            <div class="table-responsive" style="padding-bottom: 50px;">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <td class="text-left" style="width:95px;"><?php if($sort == 'order_id'){ ?> <a
                                                    href="<?php echo $sort_order_id; ?>"
                                                    class="<?php echo strtolower($order); ?>"><?php echo $column_order_id; ?></a> <?php } else { ?>
                                            <a href="<?php echo $sort_order_id; ?>"><?php echo $column_order_id; ?></a> <?php } ?>
                                        </td>
                                        <td class="text-left"><?php if($sort == 'transaction_id'){ ?> <a
                                                    href="<?php echo $sort_transaction_id; ?>"
                                                    class="<?php echo strtolower($order); ?>"><?php echo $column_transaction_id; ?></a> <?php } else { ?>
                                            <a href="<?php echo $sort_transaction_id; ?>"><?php echo $column_transaction_id; ?></a> <?php } ?>
                                        </td>
                                        <td class="text-left"
                                                style="width:100px;"><?php if($sort == 'transaction_type'){ ?> <a
                                                    href="<?php echo $sort_transaction_type; ?>"
                                                    class="<?php echo strtolower($order); ?>"><?php echo $column_transaction_type; ?></a> <?php } else { ?>
                                            <a href="<?php echo $sort_transaction_type; ?>"><?php echo $column_transaction_type; ?></a> <?php } ?>
                                        </td>
                                        <td class="text-left" style="width:150px;"><?php if($sort == 'date_added'){ ?>
                                            <a href="<?php echo $sort_date_added; ?>"
                                                    class="<?php echo strtolower($order); ?>"><?php echo $column_date_added; ?></a> <?php } else { ?>
                                            <a href="<?php echo $sort_date_added; ?>"><?php echo $column_date_added; ?></a> <?php } ?>
                                        </td>
                                        <td class="text-right"
                                                style="width:100px;"><?php echo $column_order_amount; ?></td>
                                        <td class="text-right"
                                                style="width:100px;"><?php echo $column_transaction_amount; ?></td>
                                        <td class="text-right"
                                                style="width:100px;"><?php echo $column_total_amount; ?></td>
                                        <td class="text-right" style="width:100px;"><?php echo $column_action; ?></td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if($transactions){ ?>
                                    <?php foreach($transactions as $trans){
?>
                                    <tr>
                                        <td class="text-center"><a href="<?php echo $trans['order_link']; ?>"
                                                    target="_blank"><?php echo $trans['order_id']; ?></a></td>
                                        <td class="text-left"><?php echo $trans['transaction_id']; ?></td>
                                        <td class="text-left"><?php echo $trans['transaction_type']; ?></td>
                                        <td class="text-left"><?php echo $trans['date_added']; ?></td>
                                        <td class="text-right"><?php echo $trans['order_amount']; ?></td>
                                        <td class="text-right"><?php echo $trans['transaction_amount']; ?></td>
                                        <td class="text-right"><?php echo $trans['total_amount']; ?></td>
                                        <td class="text-right">
                                            <div class="btn-group">
                                                <a href="<?php echo $trans['action_history']; ?>" data-toggle="tooltip"
                                                        title="<?php echo $button_history; ?>"
                                                        class="btn btn-default gethistory"><i class="fa fa-eye"></i></a>
                                                <?php if(($trans['transaction_type'] != 'Refund' || $trans['allowed_refund']>0) && $trans['transaction_type'] != 'Void'){ ?>

                                                <button type="button" data-toggle="dropdown"
                                                        class="btn <?php if($trans['transaction_type'] == 'Authorize'){ ?>btn-primary<?php } else{ ?>btn-default<?php } ?> dropdown-toggle">
                                                    <span class="caret"></span></button>
                                                <ul class="dropdown-menu dropdown-menu-right newtransaction">
                                                    <?php if($trans['transaction_type'] == 'Authorize'){ ?>
                                                    <li><a href="javascript:;" data-type="Capture"
                                                                data-ref="<?php echo $trans['transaction_id']; ?>"
                                                                data-amount="<?php echo $trans['allowed_capture']; ?>"
                                                                data-currency="<?php echo strtoupper($trans['currency']); ?>"><i
                                                                    class="fa fa-pencil"></i> <?php echo $button_capture; ?>
                                                        </a></li>
                                                    <li><a href="javascript:;" data-type="Void"
                                                                data-ref="<?php echo $trans['transaction_id']; ?>"
                                                                data-amount="<?php echo $trans['allowed_capture']; ?>"
                                                                data-currency="<?php echo strtoupper($trans['currency']); ?>"><i
                                                                    class="fa fa-trash-o"></i> <?php echo $button_void; ?>
                                                        </a></li>
                                                    <?php } else{ ?>
                                                    <li><a href="javascript:;" data-type="Refund"
                                                                data-ref="<?php echo $trans['transaction_id']; ?>"
                                                                data-amount="<?php echo $trans['allowed_refund']; ?>"
                                                                data-currency="<?php echo strtoupper($trans['currency']); ?>"><i
                                                                    class="fa fa-trash-o"></i> <?php echo $button_refund; ?>
                                                        </a></li>

                                                    <?php } ?>
                                                </ul>
                                                <?php } ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                    <?php } else { ?>
                                    <tr>
                                        <td class="text-center" colspan="8"><?php echo $text_no_results; ?></td>
                                    </tr>
                                    <?php } ?>
                                    </tbody>

                                </table>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
                            <div class="col-sm-6 text-right"><?php echo $results; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript"><!--
        $('#button-filter').on('click', function () {
            url = '';
            var filter_order_id = $('input[name=\'filter_order_id\']').val();
            if (filter_order_id) {
                url += '&filter_order_id=' + encodeURIComponent(filter_order_id);
            }
            var filter_transaction_id = $('input[name=\'filter_transaction_id\']').val();
            if (filter_transaction_id) {
                url += '&filter_transaction_id=' + encodeURIComponent(filter_transaction_id);
            }
            var filter_transaction_type = $('select[name=\'filter_transaction_type\']').val();
            if (filter_transaction_type !== '') {
                url += '&filter_transaction_type=' + encodeURIComponent(filter_transaction_type);
            }
            var filter_date_added = $('input[name=\'filter_date_added\']').val();
            if (filter_date_added) {
                url += '&filter_date_added=' + encodeURIComponent(filter_date_added);
            }
            location = 'index.php?route=extension/payment/lunar/payments&<?php echo $url_token_param; ?>' + url;
        });
        //--></script>

    <script src="view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <link href="view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css" type="text/css" rel="stylesheet"
            media="screen"/>
    <script type="text/javascript"><!--
        $('.date').datetimepicker({
            pickTime: false
        });
        //--></script>


    <div class="modal fade" id="lunar_payments" role="dialog">
        <div class="modal-dialog modal-md vertical-align-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <div id="plt-transaction" class="form-horizontal">
                        <p><?php echo $popup_description; ?></p>
                        <div class="form-group">
                            <label class="col-sm-4 text-right"><?php echo $popup_transaction_id; ?></label>
                            <div class="col-md-8">
                                <input class="form-control input-sm" id="plt-ref" name="ref" type="text" value=""
                                        readonly/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 text-right"><?php echo $popup_transaction_type; ?></label>
                            <div class="col-md-8">
                                <input class="form-control input-sm" id="plt-type" name="type" type="text" value=""
                                        readonly/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 text-right"><?php echo $popup_amount; ?> (<span id="plt-c"></span>):</label>
                            <div class="col-md-8">
                                <input class="form-control input-sm" id="plt-amount" name="amount" type="text" value=""
                                        readonly/>
                            </div>
                        </div>
                        <div id="plt-result"></div>
                    </div>
                    <div id="plt-history"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="button_close" data-dismiss="modal"
                            class="btn btn-default pull-left"><?php echo $popup_close; ?></button>
                    <button type="button"
                            class="btn btn-primary pull-right runtransaction"><?php echo $popup_execute; ?></button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript"><!--
        $('body').on('click', '.newtransaction a', function (e) {
            e.preventDefault();
            $('#lunar_payments #plt-history').html('');
            $('#lunar_payments .runtransaction').show();
            $('#lunar_payments #plt-history').hide();
            $('#lunar_payments #plt-transaction').show();
            $('#lunar_payments #plt-result').html('');
            $('#lunar_payments .modal-title').text('<?php echo $popup_title_transaction; ?>');
            $('#lunar_payments #plt-transaction > p').text('<?php echo $popup_description; ?>');
            $('#lunar_payments #plt-ref').val($(this).data('ref'));
            $('#lunar_payments #plt-type').val($(this).data('type'));
            $('#lunar_payments #plt-amount').val($(this).data('amount'));
            $('#lunar_payments #plt-c').text($(this).data('currency'));
            if ($(this).data('type') == 'Refund') {
                $('#lunar_payments #plt-amount').attr('readonly', false);
            } else {
                $('#lunar_payments #plt-amount').attr('readonly', true);
            }
            $('#lunar_payments').modal('show');
        });

        $('body').on('click', '.runtransaction', function (e) {
            e.preventDefault();
            $.ajax({
                url: 'index.php?route=extension/payment/lunar/transaction&<?php echo $url_token_param; ?>',
                dataType: 'json',
                type: 'POST',
                data: 'ref=' + $('#lunar_payments #plt-ref').val() + '&type=' + $('#lunar_payments #plt-type').val() + '&amount=' + $('#lunar_payments #plt-amount').val(),
                success: function (data, textStatus, jQxhr) {
                    if (data.error) {
                        $('#lunar_payments #plt-result').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + data.error + '</div>');
                    } else {
                        $('#lunar_payments #plt-result').html('<div class="alert alert-success"><i class="fa fa-exclamation-circle"></i> ' + data.success + '</div>');
                        $('#lunar_payments .runtransaction').hide();
                        $('#button_close').click(function (e) {
                            e.preventDefault();
                            location.reload();
                        });
                    }
                },
                error: function (jqXhr, textStatus, errorThrown) {
                    console.log(errorThrown);
                    $('#lunar_payments #plt-result').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + errorThrown + '</div>');
                }
            });
        });

        $('body').on('click', '.gethistory', function (e) {
            e.preventDefault();
            $('#lunar_payments #plt-history').html('');
            $('#lunar_payments .runtransaction').hide();
            $('#lunar_payments #plt-result').html('');
            $('#lunar_payments #plt-transaction').hide();
            $('#lunar_payments #plt-history').show();
            $('#lunar_payments #plt-ref').val('');
            $('#lunar_payments #plt-type').val('');
            $('#lunar_payments #plt-amount').val('');
            $('#lunar_payments #plt-c').text('');
            $('#lunar_payments').modal('show');
            $.ajax({
                url: $(this).attr('href'),
                dataType: 'html',
                type: 'GET',
                success: function (data, textStatus, jQxhr) {
                    $('#lunar_payments #plt-history').html(data);
                },
                error: function (jqXhr, textStatus, errorThrown) {
                    console.log(errorThrown);
                }
            });
        });
        //--></script>

</div>
<?php echo $footer; ?>
