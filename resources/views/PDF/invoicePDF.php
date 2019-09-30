<?php

  $paymentimages = asset('public/images/Image.jpg');
  $paidpng = asset('public/images/paid.png');

  $totalCollections = $invoice->totalCollections;
  $invoice_amount = $invoice->invoice_amount;
  $totalTax = $invoice_amount * $invoice->tax_percentage / 100;
  $totalInvoiceAmount = $invoice_amount + ($totalTax);
  $dueBalance = $totalInvoiceAmount - $totalCollections;
?>
<!DOCTYPE html>
<html>
   <head>
      <style>         
        .pdf_outr {
          table-layout: fixed;
          width: 100%;
        }
      </style>
   </head>
   <body>
      <table Width="100%"  cellspacing="0" style="font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000;">
         <tr style="text-align:center">
            <td></td>
            <p>&nbsp;</p>
            <td></td>
         </tr>
         <tr style="text-align:center">
            <td align="left" width="20%">
               <img width="150px " src="<?php echo asset($company->comp_logo);?>" />
            </td>
            <td width="57%">
               <table cellspacing="3">
                  <tr>
                     <td style="font-size:12px;text-align:left;">&nbsp;</td>
                  </tr>
                  <tr>
                     <td style="font-size:12px;text-align:left;">&nbsp;</td>
                  </tr>
                  <tr>
                     <td style="font-size:18px;text-align:center;color:black; font-weight: 600"> <?php echo $company->comp_name;?></td>
                  </tr>
                  <?php if($company->comp_address) { ?> 
                  <tr>
                     <td style="font-size:12px;text-align:center;color:green"><?php echo $company->comp_address;?></td>
                  </tr>
                  <?php } if($company->tag_line) { ?>
                  <tr>
                     <td style="font-size:12px;text-align:center;color:red"><i><?php echo $company->tag_line;?></i></td>
                  </tr>
                  <?php } ?>
                  <tr>
                     <td style="font-size:12px;text-align:center;color:blue"><a href="www.canadarestorationservices.com" target="_blank">www.canadarestorationservices.com</a></td>
                  </tr>
               </table>
            </td>
            <td align="right" style="text-align:left" width="23%">
               <table  cellspacing="3" >
                  <tr align="right">
                     <td style="font-size:35px;text-align:right;color:#ccc">Invoice</td>
                  </tr>
                  <tr>
                     <td style="font-size:12px;text-align:left;"><b>Invoice:</b> <?php echo $invoice->invoice_no;?></td>
                  </tr>
                  <tr>
                     <td style="font-size:12px;text-align:left;"><b>Due Date:</b> <?php echo $invoice->due_date;?></td>
                  </tr>
                  <?php if($invoice->invoice_amount && $dueBalance == '0') { ?>
                  <tr>
                     <td><img src="<?php echo $paidpng;?>" width="100px"></td>
                  </tr>
                  <?php } ?>
               </table>
            </td>
         </tr>
         <tr style="" rowspan="3">
            <td colspan="3">&nbsp;</td>
         </tr>
         <tr style="" rowspan="3">
            <td colspan="3">&nbsp;</td>
         </tr>
         <tr style="">
            <td align="right" width="71%">
               <table cellspacing="3">
                  <tr>
                     <td style="font-size:16px;text-align:left;"><b>Invoice For</b></td>
                  </tr>
                  <tr>
                     <td style="font-size:12px;text-align:left;">Name: <?php echo $customer->customer_name;?></td>
                  </tr>
                  <tr>
                     <td style="font-size:12px;text-align:left;">Address: <?php echo $customer->customer_address;?><?php echo ' '.$customer->postal_code;?></td>
                  </tr>
                  <tr>
                     <td style="font-size:12px;text-align:left;">Phone:  <?php echo $customer->phone;?></td>
                  </tr>
                  <tr>
                     <td style="font-size:12px;text-align:left;">Email: <?php echo $customer->email;?></td>
                  </tr>
               </table>
            </td>
            <td align="right" style="text-align:left" width="29%">
               <table  cellspacing="3" >
                  <tr>
                     <td style="font-size:12px;text-align:left;">&nbsp;</td>
                  </tr>
                  <?php  if($company->comp_gst_no){ ?>
                  <tr>
                     <td style="font-size:12px;text-align:left;"><b>Gst:</b>  <?php echo $company->comp_gst_no;?></td>
                  </tr>
                  <?php } ?>
                  <?php  if($company->comp_qst_no){ ?>
                  <tr>
                     <td style="font-size:12px;text-align:left;"><b>Qst:</b>  <?php echo $company->comp_qst_no;?></td>
                  </tr>
                  <?php } ?>
                  <?php  if($invoice->po_claim){ ?>
                  <tr>
                     <td style="font-size:12px;text-align:left;"><b>PO/Claim:</b>  <?php echo $invoice->po_claim;?></td>
                  </tr>
                  <?php } ?>
                  <tr>
                     <td style="font-size:12px;text-align:left;"><b>Est:</b> <?php echo $invoice->estimate_id;?></td>
                  </tr>
                  <tr>
                     <td style="font-size:12px;text-align:left;"><b>Submitted Date:</b> <?php echo date('d-m-Y');?></td>
                  </tr>
               </table>
            </td>
         </tr>
         <tr>
            <td colspan="3">
               <p>&nbsp;</p>
            </td>
         </tr>
         <tr>
            <td colspan="3">
               <p>&nbsp;</p>
            </td>
         </tr>
      </table>
      <table class="content-tbl" width="100%" cellpadding="6">
         <thead>
            <tr>
               <th style="text-align: center; padding: 10px 15px; border: 1px solid #000;  border-bottom:none; border-right: none; font-size: 12px;">S.No</th>
               <th style="text-align: center; padding: 10px 15px; border: 1px solid #000;  border-bottom: none; border-right: none;  font-size: 12px;">Service type</th>
               <th style="text-align: center; padding: 10px 15px; border: 1px solid #000;  border-bottom: none; border-right:none; font-size: 12px;">Amount</th>
            </tr>
         </thead>
         <tbody>
            <tr>
               <td style="padding: 5px; text-align: center;border: 1px solid #000; border-right: none; border-bottom: none; font-size: 12px;"><?php echo $invoice->invoice_no;?></td>
               <td  style="padding: 5px; text-align: center; border: 1px solid #000; border-right: none; border-bottom: none; font-size: 12px;"><?php echo $invoice->service;?></td>
               <td  style="padding: 5px; text-align: center;  border: 1px solid #000; border-right:none; border-bottom:none;  font-size: 12px;"><?php echo $invoice->invoice_amount;?></td>
            </tr>
         </tbody>
      </table>
      <table class="notes" width="100%" cellpadding="5">
         <tbody>
            <tr>
               <th style="border-bottom:3px solid #000;text-align: left; padding:15px 0;"><br>Notes:<br><?php echo $invoice->note;?></th>
            </tr>
            <tr>
               <td colspan="2">
                  <p>&nbsp;</p>
               </td>
            </tr>
            <tr width="100%">
               <td width="" style="text-align:right;font-size:14px;"><strong>Sub total: </strong>$<?php echo $invoice->invoice_amount;?></td>
            </tr>
            <tr width="100%">
               <td width="" style="text-align: right; letter-spacing: 1px;font-size:14px;"><strong><?php echo $invoice->tax_name; ?>(<?php echo $invoice->tax_percentage ?>%): </strong>$<?php echo $totalTax;?></td>
            </tr>
            <tr width="100%">
               <td width="" style="text-align: right; letter-spacing: 1px;font-size:14px;"><strong>TOTAL: </strong>$<?php echo $totalInvoiceAmount;?></td>
            </tr>
            <tr>
               <td width="" style="text-align: right; letter-spacing: 1px;font-size:14px;"><strong>PAYMENT: </strong>$<?php echo $totalCollections;?></td>
            </tr>
            <tr>
               <td width="" style="text-align: right; letter-spacing: 1px;font-size:14px;"><strong>BALANCE DUE: </strong>$<?php echo $dueBalance;?></td>
            </tr>
            <tr>
               <td colspan="2">
                  <p>&nbsp;</p>
               </td>
            </tr>
            <tr width="100%" class="last-sec">
               <td align="left" colspan="2">
                  <img  src="<?php echo $paymentimages; ?>" />
               </td>
            </tr>
            <tr>
               <td colspan="2">
                  <p>&nbsp;</p>
               </td>
            </tr>
         </tbody>
      </table>
      <p>
         Account Summary
      </p>
      <table class="content-tbl" width="100%" cellpadding="6">
         <thead>
            <tr>
               <th style="text-align: center; padding: 10px 15px; border: 1px solid #000;  border-bottom:none; border-right: none; font-size: 12px;">S.No</th>
               <th style="text-align: center; padding: 10px 15px; border: 1px solid #000;  border-bottom: none; border-right: none;  font-size: 12px;">Invoice Date</th>
               <th style="text-align: center; padding: 10px 15px; border: 1px solid #000;  border-bottom: none; border-right:none; font-size: 12px;">Amount</th>
               <th style="text-align: center; padding: 10px 15px; border: 1px solid #000;  border-bottom: none; border-right: none;  font-size: 12px;">Invoice Balance</th>
               <th style="text-align: center; padding: 10px 15px; border: 1px solid #000;  border-bottom: none; border-right: none;  font-size: 12px;">Service</th>
               <th style="text-align: center; padding: 10px 15px; border: 1px solid #000;  border-bottom: none; border-right: none;  font-size: 12px;">Aged Invoice</th>
            </tr>
         </thead>
         <tbody>
            <?php   
               foreach ($invoices as $invoice) { 
            ?>
            <tr>
               <td style="padding: 5px; text-align: center;border: 1px solid #000; border-right: none; border-bottom: none; font-size: 12px;"><?php echo $invoice->invoice_no;?></td>
               <td style="padding: 5px; text-align: center; border: 1px solid #000; border-right: none; border-bottom: none; font-size: 12px;"><?php echo $invoice->invoice_date;?></td>
               <td style="padding: 5px; text-align: center;  border: 1px solid #000; border-right:none; border-bottom:none; font-size: 12px;"><?php echo $invoice->invoice_amount;?></td>
               <td style="padding: 5px; text-align: center;  border: 1px solid #000; border-right: none; border-bottom: none;font-size: 12px;">$<?php echo $invoice->invoice_amount - $invoice->totalCollections;?></td>
               <td style="padding: 10px;text-align: center;  border: 1px solid #000; border-right: none; border-bottom: none;font-size: 12px;"><?php echo  $invoice->service;?></td>
               <td style="padding: 10px;text-align: center;  border: 1px solid #000; border-right: none; border-bottom: none;font-size: 12px;"><?php echo  $invoice->invoice_date;?></td>
            </tr>
            <?php
               }
               ?>
         </tbody>
      </table>
   </body>
</html>