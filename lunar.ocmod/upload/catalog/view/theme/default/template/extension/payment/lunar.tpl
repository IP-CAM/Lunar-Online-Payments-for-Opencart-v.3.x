<?php if($active_mode=='test'){ ?>
    <div class="alert alert-info attention"><i class="fa fa-exclamation-circle"></i>&nbsp;<?php echo $warning_test_mode; ?></div>
<?php } ?>
<div data-amount="<?php echo $amount; ?>" style="float: left" id="lunar-payment-widget"></div>
<div class="buttons">
  <div class="pull-right">
    <input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="btn btn-primary" />
  </div>
</div>
<script type="text/javascript" src="https://sdk.paylike.io/a.js"></script>
<script type="text/javascript"><!--
    $('#button-confirm').on('click', function() {
      $('.payment-alert').remove();

      var sdkClient = Paylike({key: '<?php echo $lunar_public_key; ?>'});

      /* Integration with Journal 3 Theme - One Page Checkout
         * Disable preloader if is defined
         */
      try {
        $('.journal-loading-overlay').remove();
        triggerLoadingOff();
      } catch (error) {}

      sdkClient.pay({
        test: ('live' === '<?php echo $active_mode; ?>') ? (false) : (true),
        title: '<?php echo $popup_title; ?>',
        description: '<?php echo $popup_description; ?>',
        amount: {
          currency: '<?php echo strtoupper($currency_code); ?>',
          exponent: <?php echo $exponent; ?>,
          value: <?php echo $amount; ?>
        },
        locale: '<?php echo $lc; ?>',
        custom: {
          orderId: '<?php echo $order_id; ?>',
          products: <?php echo $products; ?>,
          customer: {
            name: '<?php echo $name; ?>',
            email: '<?php echo $email; ?>',
            phoneNo: '<?php echo $telephone; ?>',
            address: '<?php echo $address; ?>',
            IP: '<?php echo $ip; ?>'
          },
          platform: {
            name: 'opencart',
            version: '<?php echo $opencart_version; ?>'
          },
          lunarPluginVersion: '<?php echo $plugin_version; ?>'
        }
      }, function(err, res) {
        if (err)
          return console.log(err);

        console.log(res);
        console.log('++++++++++++++++++++++++++++');

        $.ajax({
          url: 'index.php?route=extension/payment/lunar/process_payment',
          type: 'post',
          data: {
            'trans_ref': res.transaction.id
          },
          dataType: 'json',
          cache: false,
          beforeSend: function() {
            $('#button-confirm').button('loading');
          },
          complete: function() {
            $('#button-confirm').button('reset');
            alert(1);
          },
          success: function(json) {
            if (json.hasOwnProperty('error')) {
              var html = '<div class="alert alert-danger payment-alert"><i class="fa fa-exclamation-circle"></i> Warning: ' + json.error + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
              $('#button-confirm').closest('.buttons').before(html);
            }
            if (json.hasOwnProperty('redirect')) {
              var html = '<div class="alert alert-success payment-alert"><i class="fa fa-exclamation-circle"></i> ' + json.success + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
              $('#button-confirm').closest('.buttons').before(html);
              location.href = json.redirect;
            }
          }
        });
      });
    });
//--></script>
