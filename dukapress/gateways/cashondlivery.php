<?php
class Dpsc_CashOnDelivery extends DukaPress_Gateway{

  function on_create(){
		global $dukapress;
		$this->plugin_name = __('Cash on Delivery','dp-lang');
		$this->plugin_slug = 'cashondelivery';
	}

  function set_up_options($settings){
    
  }
}
?>
