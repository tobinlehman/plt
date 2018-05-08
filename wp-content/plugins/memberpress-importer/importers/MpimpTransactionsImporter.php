<?php
if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

class MpimpTransactionsImporter extends MpimpBaseImporter {
  public function form() {
    return; //Temporarily disable this since it's not even used as of right now
    ?>
    <input type="checkbox" name="args[welcome]" />&nbsp; <?php _e('Send a Welcome Email with each new transaction'); ?>
    <?php
  }

  public function import($row,$args) {
    $required = array(
                  array( 'any' => array('username','email') ),
                  'product_id', 'amount'
                );
    $this->check_required('transactions', array_keys($row), $required);

    $this->fail_if_empty('product_id', $row['product_id']);

    // Merge in default values where cols missing
    $row = array_merge( array( 'trans_num' => uniqid(),
                               'status' => MeprTransaction::$complete_str,
                               'sub_num' => 0,
                               'expires_at' => null,
                               'coupon_code' => '',
                               'payment_method' => MeprTransaction::$manual_gateway_str,
                               'send_welcome' => 0 ), $row );

    $txn = new MeprTransaction();
    $txn->txn_type = MeprTransaction::$payment_str;

    $valid_statuses = array( MeprTransaction::$pending_str,
                             MeprTransaction::$failed_str,
                             MeprTransaction::$complete_str,
                             MeprTransaction::$refunded_str );

    foreach( $row as $col => $cell ) {
      switch( $col ) {
        case "product_id":
          $this->fail_if_not_valid_product_id($cell);
          $prd = new MeprProduct($cell);
          $txn->product_id = $prd->ID;
          break;
        case "username":
        case "email":
          $this->fail_if_empty($col, $cell);
          $usr = new MeprUser();

          if( $col == "username" ) {
            $usr->load_user_data_by_login($cell);
            if(!$usr->ID)
              throw new Exception(sprintf(__('username=%1$s wasn\'t found so couldn\'t create transaction'), $cell));
          }
          else {
            $usr->load_user_data_by_email($cell);
            if(!$usr->ID)
              throw new Exception(sprintf(__('email=%1$s wasn\'t found so couldn\'t create transaction'), $cell));
          }

          $txn->user_id = $usr->ID;
          break;
        case "amount":
          $this->fail_if_empty($col, $cell);
          $this->fail_if_not_number($col, $cell);
          $txn->amount = $cell;
          break;
        case 'sub_num':
          if(!empty($cell)) {
            $this->fail_if_not_valid_sub_num($cell);
            if($sub = MeprSubscription::get_one_by_subscr_id($cell)) {
              $txn->subscription_id = $sub->ID;
            }
          }
          break;
        case 'payment_method':
          if(!empty($cell)) {
            $this->fail_if_not_valid_payment_method($cell);
            $txn->gateway = $cell;
          }
          break;
        case 'coupon_code':
          if(!empty($cell) and $cpn = MeprCoupon::get_one_from_code($cell)) {
            $this->fail_if_not_valid_coupon_code($cell);
            $txn->coupon_id = $cpn->ID;
          }
          else
            $txn->coupon_id = 0;
          break;
        case 'send_welcome':
          $send_welcome = ((int)$cell==1);
          break;
        case 'trans_num':
          $txn->trans_num = empty($cell)?uniqid():$cell;
          break;
        case 'status':
          $txn->status = empty($cell)?MeprTransaction::$complete_str:$cell;
          $this->fail_if_not_in_enum($col,$cell,$valid_statuses);
          break;
        case 'created_at':
          $txn->created_at = $cell;
          break;
        case 'expires_at':
          if(!empty($cell)) {
            $txn->expires_at = $cell;
            $this->fail_if_not_date($col, $cell);
          }
          break;
      }
    }

    $txn_id = $txn->store();

    //Record the completed txn event
    if($txn->status == MeprTransaction::$complete_str) {
      MeprEvent::record('transaction-completed', $txn);
    }

    $mepr_options = MeprOptions::fetch();

    if( $txn_id ) {
      if( $send_welcome ) {
        $params = array( 'user_id'          => $usr->ID,
                         'user_login'       => $usr->user_login,
                         'username'         => $usr->user_login,
                         'user_email'       => $usr->user_email,
                         'user_first_name'  => $usr->first_name,
                         'user_last_name'   => $usr->last_name,
                         'membership_type'  => $prd->post_title,
                         'product_name'     => $prd->post_title,
                         'invoice_num'      => $txn->id,
                         'trans_num'        => $txn->trans_num,
                         'user_remote_addr' => $_SERVER['REMOTE_ADDR'],
                         'payment_amount'   => sprintf("\\$%0.2f", $txn->amount),
                         'blog_name'        => get_bloginfo('name'),
                         'business_name'    => get_bloginfo('name'),
                         'login_page'       => $mepr_options->login_page_url() );

        $params = apply_filters( 'mepr_gateway_notification_params', $params, $txn );

        MeprUtils::send_admin_signup_notification($params);
        MeprUtils::send_user_signup_notification($params);

        if( (float)$txn->amount > 0.00 ) { MeprUtils::send_user_receipt_notification($params); }
      }

      return sprintf( __('Transaction (id = %s) was created successfully'), $txn_id );
    }
    else
      throw new Exception( __('Transaction failed to be created') );
  }
}
