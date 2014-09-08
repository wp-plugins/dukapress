<?php
/**
 * This function generates PDF
 *
 */
function make_pdf($invoice, $dpsc_discount_value, $tax, $dpsc_shipping_value, $dpsc_total, $bfname, $blname, $bcity, $baddress, $bstate, $bzip, $bcountry, $phone, $option='bill', $test=0) {
    global $dpsc_country_code_name;
    $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
    define('FPDF_FONTPATH', DP_PLUGIN_DIR.'/lib/fpdf16/font/');
    require_once(DP_PLUGIN_DIR.'/lib/fpdf16/fpdf.php');


    if ($option == 'bill') {

        class PDF extends FPDF {

            //Page header
            function Header() {
                $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
                $ad = array();
                $ad[f_name] = $dp_shopping_cart_settings['shop_name'];
                $ad[street] = $dp_shopping_cart_settings['shop_address'];
                $ad[zip] = $dp_shopping_cart_settings['shop_zip'];
                $ad[town] = $dp_shopping_cart_settings['shop_city'];
                $ad[state] = $dp_shopping_cart_settings['shop_state'];


                $biz_ad = implode("<br/>", $ad);
                $biz = str_replace("<br/>", "\n", $biz_ad);
                $biz = pdf_encode($biz);

                $this->SetFont('Arial', 'B', 12);

                //$url  = get_option('siteurl');
                $path = DP_PLUGIN_DIR . '/images/pdf-logo-1.jpg';
                $this->Image($path);
                $this->SetXY(90, 7);
                $this->MultiCell(0, 7, "$biz", 0, 'L');
            }

            //Page footer
            function Footer() {
                $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
                //Position at xy mm from bottom
                $this->SetY(-25);
                //Arial italic 6
                $this->SetFont('Arial', '', 6);

                if (FALSE) {
                    $vat_id = ' - ' . get_option('wps_vat_id_label') . ': ' . get_option('wps_vat_id');
                } else {$vat_id = NULL;        }

                $footer_text = $dp_shopping_cart_settings['shop_name'] . $vat_id;
                $this->Cell(0, 10, "$footer_text", 1, 0, 'C');
            }

        }

        //Instanciation of inherited class
        $pdf = new PDF;
        $pdf->SetLeftMargin(10);
        $pdf->SetRightMargin(10);
        $pdf->SetTopMargin(5);

        // widths of columns
        $w1 = 20;
        $w2 = 64;
        $w3 = 30;
        $w4 = 38;
        $w5 = 38;

        $h2 = 3;


        $pdf->AddPage();
        $pdf->Ln(20);
        $pdf->SetFont('Arial', '', 10);
        // data for address
        $order = array();
        $order[f_name] = $bfname . ' ' . $blname;
//        $order[l_name] = $blname;
        $order[street] = $baddress;
        $order[town] = $bcity;
        $order[state] = $bstate;
        $order[zip] = $bzip;

        $order[country] = $dpsc_country_code_name[$bcountry];

        address_format($order, 'pdf_cust_address', $pdf);


        $pdf->Ln(20);
        $pdf->SetFont('Arial', 'B', 10);
        $phone_no = pdf_encode('Contact No. : ' . $phone);
        $pdf->Cell(0, 6, $phone_no, 0, 1);
        $bill_no = pdf_encode('Bill No. : ' . $invoice);
        $pdf->Cell(0, 6, $bill_no, 0, 1);

        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 4, date(get_option('date_format')), 0, 1, 'R');

        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($w1, 6, pdf_encode('Sr. No.'), 1, 0);
        $pdf->Cell($w2, 6, pdf_encode('Product Name'), 1, 0);
        $pdf->Cell($w3, 6, pdf_encode('Quantity'), 1, 0);
        $pdf->Cell($w4, 6, pdf_encode('Product Price'), 1, 0);
        $pdf->Cell($w5, 6, pdf_encode('Total'), 1, 1);
        $pdf->SetFont('Arial', '', 9);


        // get the cart content again
        $dpsc_products = $_SESSION['dpsc_products'];
        $dpsc_total = 0.00;
        $count = 1;
        foreach ($dpsc_products as $dpsc_product) {
            $dpsc_var = '';
            if (!empty($dpsc_product['var'])) {
                $dpsc_var = ' (' . $dpsc_product['var'] . ')';
            }
            $dpsc_total += floatval($dpsc_product['price'] * $dpsc_product['quantity']);
            $dis_price = number_format($dpsc_product['price'], 2);
            $dis_price_total = number_format($dpsc_product['price'] * $dpsc_product['quantity'], 2);
            $details = explode("|", $v);

            $pdf->SetFont('Arial', 'B', 9);

            $pdf->Cell($w1, 6, pdf_encode("$details[5]"), 'LTR', 0); // Art-no
            $pdf->Cell($w2, 6, pdf_encode("$details[2]"), 'LTR', 0); // Art-name
            $pdf->Cell($w3, 6, pdf_encode("$details[1]"), 'LTR', 0); // Amount
            $pdf->Cell($w4, 6, pdf_encode("$details[3]"), 'LTR', 0); // U - Price
            $pdf->Cell($w5, 6, pdf_encode("$details[4]"), 'LTR', 1); // Total price
            // any attributes?
            $pdf->SetFont('Arial', '', 7);

        //				foreach($ad as $v){
        //
        //					if(WPLANG == 'de_DE'){$v = utf8_decode($v);}
        //					pdf_encode($v);

            $pdf->Cell($w1, $h2, $count, 'LR', 0); // Art-no
            $pdf->Cell($w2, $h2, $dpsc_product['name'] . $dpsc_var, 'LR', 0); // Art-name
            $pdf->Cell($w3, $h2, $dpsc_product['quantity'], 'LR', 0); // Amount
            $pdf->Cell($w4, $h2, pdf_encode($dp_shopping_cart_settings['dp_currency_symbol'] . $dis_price), 'LR', 0); // U - Price
            $pdf->Cell($w5, $h2, pdf_encode($dp_shopping_cart_settings['dp_currency_symbol'] . $dis_price_total), 'LR', 1); // Total price
            //}
            // ending line of article row
            $pdf->Cell($w1, 1, "", 'LBR', 0); // Art-no
            $pdf->Cell($w2, 1, "", 'LBR', 0); // Art-name
            $pdf->Cell($w3, 1, "", 'LBR', 0); // Amount
            $pdf->Cell($w4, 1, "", 'LBR', 0); // U - Price
            $pdf->Cell($w5, 1, "", 'LBR', 1); // Total price
        }
        $pdf->SetFont('Arial', '', 9);

 
        $total = $dpsc_total;

        if ($dpsc_discount_value > 0) {
            $total_discount = $total * $dpsc_discount_value / 100;
        } else {
            $total_discount = 0.00;
        }
        if ($tax > 0) {
            $total_tax = ($total - $total_discount) * $tax / 100;
        } else {
            $total_tax = 0.00;
        }
        $shipping = $dpsc_shipping_value;
        $amount = number_format($total + $shipping + $total_tax - $total_discount, 2);
        $netsum_str = 'Subtotal:' . ' ' . $dp_shopping_cart_settings['dp_currency_symbol'] . number_format($total,2) . ' ' . $dp_shopping_cart_settings['dp_shop_currency'];
        $pdf->Cell(0, 6, pdf_encode($netsum_str), 0, 1, 'R');

        // discount
        $disf_str = pdf_encode('- Discount:') . ' ' . $dp_shopping_cart_settings['dp_currency_symbol'] . number_format($total_discount,2) . ' ' . $dp_shopping_cart_settings['dp_shop_currency'];
        $pdf->Cell(0, 6, pdf_encode($disf_str), 0, 1, 'R');
        // discount
        $taxf_str = pdf_encode('+ Tax:') . ' ' . $dp_shopping_cart_settings['dp_currency_symbol'] . number_format($total_tax,2) . ' ' . $dp_shopping_cart_settings['dp_shop_currency'];
        $pdf->Cell(0, 6, pdf_encode($taxf_str), 0, 1, 'R');
        // shipping fee
        $shipf_str = pdf_encode('+ Shipping fee:') . ' ' . $dp_shopping_cart_settings['dp_currency_symbol'] . number_format($shipping,2) . ' ' . $dp_shopping_cart_settings['dp_shop_currency'];
        $pdf->Cell(0, 6, pdf_encode($shipf_str), 0, 1, 'R');


        $pdf->SetFont('Arial', 'B', 9);
        $totf_str = pdf_encode('Total:') . ' ' . $dp_shopping_cart_settings['dp_currency_symbol'] . $amount . ' ' . $dp_shopping_cart_settings['dp_shop_currency'];
        $pdf->Cell(00, 6, pdf_encode($totf_str), 0, 1, 'R');
    } else {

    }

    $file_name = 'invoice_' . $invoice . '.pdf';
    $pdf->SetDisplayMode(100);
    $output_path = DP_PLUGIN_DIR . '/pdf/' . $file_name;
    //$output_path_test	= PDF_PLUGIN_URL.'pdfinner/bills/test.pdf';

    if ($test == 0) {
        $pdf->Output($output_path, 'F');
    } else {
        $pdf->Output($output_path_test, 'F');
    }
}

function pdf_encode($data) {

	$data = mb_convert_encoding($data, "iso-8859-1", "auto");
	// utf8_decode() might be also interesting...

	return $data;
}

function address_format($ad, $option='html', $pdf=0) {

	$address = NULL;
	$name = $ad[f_name];
	if (strpos($address, 'NAME') !== false) {
		$address = str_replace("NAME", strtoupper($name), $address);
	}
	if (strpos($address, 'name') !== false) {
		$address = str_replace("name", $name, $address);
	}

	$address = address_token_replacer($address, 'STREET', $ad);
	$address = address_token_replacer($address, 'HSNO', $ad);
	$address = address_token_replacer($address, 'STRNO', $ad);
	$address = address_token_replacer($address, 'STRNAM', $ad);
	$address = address_token_replacer($address, 'PB', $ad);
	$address = address_token_replacer($address, 'PO', $ad);
	$address = address_token_replacer($address, 'PZONE', $ad);
	$address = address_token_replacer($address, 'CROSSSTR', $ad);
	$address = address_token_replacer($address, 'COLONYN', $ad);
	$address = address_token_replacer($address, 'DISTRICT', $ad);
	$address = address_token_replacer($address, 'REGION', $ad);
	$address = address_token_replacer($address, 'PLACE', $ad);
	$address = address_token_replacer($address, 'STATE', $ad);
	$address = address_token_replacer($address, 'ZIP', $ad);
	$address = address_token_replacer($address, 'COUNTRY', $ad);

	foreach ($ad as $p) {
		$pdf->Cell(0, 6, utf8_decode($p), 0, 1);
	}
	return $address;
}

function address_token_replacer($address, $needle, $replace) {
	$needle_lower = strtolower($needle);
	$key = $needle_lower;
	if (($needle == 'PLACE') || ( $needle == 'place')) {
		$key = 'town';
	}

	if (stripos($address, $needle) !== false) {
	if (strpos($address, $needle) !== false) {
		$address = str_replace($needle, mb_strtoupper($replace["$key"]), $address);
	} else {
		$address = str_replace($needle_lower, $replace["$key"], $address);
	}
	}
	return $address;
}
?>