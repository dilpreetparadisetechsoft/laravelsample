<?php

function SendEmail($emailTo = '', $EmailSubject = '', $emailBody = '', array $attachments = [], $emailFromName = '', $emailFromEmail = '', $email_cc = '', $email_bcc = '')
{  
    \Mail::send(
        'EmailTemplate.Index', 
        ['html' => $emailBody], 
        function($message) 
        use($emailTo, $EmailSubject, $attachments, $emailFromName, $emailFromEmail, $email_cc, $email_bcc) 
        {
            $message->from(
                ($emailFromEmail?$emailFromEmail:'CS@Canadarestorationservices.com'), 
                ($emailFromName?$emailFromName:'BondCRM')
            )->subject($EmailSubject);

            $message->to(explode(',', $emailTo));

            if ($email_cc) {
                $message->cc(explode(',', $email_cc));
            }
            if ($email_bcc) {
                $message->cc(explode(',', $email_bcc));
            }
            if (empty($attachments) &&  is_array($attachments)) {
                foreach ($attachments as $attachment) {
                    $message->attach($attachment);
                }
            }        
        }
    );
}

function inspectionpdf($email, $customer_name, $customer_address, $event_start_time= '', $start_date, $first_name){
        
    //PDF::SetCreator(PDF_CREATOR);
    PDF::SetAuthor('Bond CRM');
    PDF::SetTitle("Bond CRM ".$customer_name);
    PDF::SetSubject("Bond CRM ".$customer_name);
    PDF::SetKeywords("Bond CRM ".$customer_name);
    PDF::setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    PDF::setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    PDF::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    PDF::SetMargins(PDF_MARGIN_LEFT, 1, PDF_MARGIN_RIGHT);
    PDF::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    PDF::setImageScale(PDF_IMAGE_SCALE_RATIO);
   
    $data['date'] = $start_date; 
    $data['time'] = $event_start_time;
    $data['address']  = $customer_address;
    $data['repname'] = $first_name;
    $html = view("PDF/inspectionPDF",$data);

    PDF::setFontSubsetting(true);
    PDF::SetPrintFooter(false);
    PDF::SetFont('times', '', 16,'','false');
    PDF::AddPage();
    PDF::writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
    $path = public_path("public/images/uploads/pdf/".date('Y')."/".date('m'));
    File::makeDirectory($path, $mode = 0777, true, true);
    $file_name= $path."/inspectionpdf-".date('YmdThis').".pdf";    
    PDF::Output($file_name, 'F');
    return $file_name;
}
function InvoiceViewPDf($invoice, $invoices, $company, $customer, $companyUser)
{
    PDF::SetAuthor('Bond CRM');
    PDF::SetTitle("Bond CRM ".$customer->customer_name);
    PDF::SetSubject("Bond CRM ".$customer->customer_name);
    PDF::SetKeywords("Bond CRM ".$customer->customer_name);
    PDF::setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    PDF::setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    PDF::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    PDF::SetMargins(PDF_MARGIN_LEFT, 1, PDF_MARGIN_RIGHT);
    PDF::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    PDF::setImageScale(PDF_IMAGE_SCALE_RATIO);

    $html = view("PDF/invoicePDF", compact('invoice','invoices','company','customer','companyUser'));

    PDF::setFontSubsetting(true);
    PDF::SetPrintFooter(false);
    PDF::SetFont('times', '', 16,'','false');
    PDF::AddPage();
    PDF::writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
    PDF::Output('invoice.pdf', 'I');
}
function estimatePDF($estimate, $estimateItems, $company, $user, $customer, $services, $title, $type, $tax)
{
    PDF::SetAuthor('Bond CRM');
    PDF::SetTitle("Bond CRM ".$customer->customer_name);
    PDF::SetSubject("Bond CRM ".$customer->customer_name);
    PDF::SetKeywords("Bond CRM ".$customer->customer_name);
    PDF::setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    PDF::setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    PDF::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    PDF::SetMargins(PDF_MARGIN_LEFT, 1, PDF_MARGIN_RIGHT);
    PDF::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    PDF::setImageScale(PDF_IMAGE_SCALE_RATIO);

    $html = view("PDF/estimatePDF", compact('estimate','estimateItems','company','user','customer','services','title','tax'));

    PDF::setFontSubsetting(true);
    PDF::SetPrintFooter(false);
    PDF::SetFont('dejavusans', '', 14,'','false');
    PDF::AddPage();
    PDF::writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
    if ($type == 'view') {
        PDF::Output('invoice.pdf', 'I');
    } elseif ($type == 'download') {
        $filename = "/invoice-pdf-".date('YmdThis').".pdf";
        $path = "public/images/uploads/estimate/pdf/".date('Y')."/".date('m');
        $publicPath = base_path($path);
        $assetPath = asset($path);
        File::makeDirectory($publicPath, $mode = 0777, true, true);
        PDF::Output($publicPath.$filename, 'F');
        return $assetPath.$filename;
    }
}
function InvoicePDf($invoice, $invoices, $company, $customer, $companyUser)
{
    PDF::SetAuthor('Bond CRM');
    PDF::SetTitle("Bond CRM ".$customer->customer_name);
    PDF::SetSubject("Bond CRM ".$customer->customer_name);
    PDF::SetKeywords("Bond CRM ".$customer->customer_name);
    PDF::setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    PDF::setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    PDF::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    PDF::SetMargins(PDF_MARGIN_LEFT, 1, PDF_MARGIN_RIGHT);
    PDF::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    PDF::setImageScale(PDF_IMAGE_SCALE_RATIO);

    $html = view("PDF/invoicePDF", compact('invoice','invoices','company','customer','companyUser'));

    PDF::setFontSubsetting(true);
    PDF::SetPrintFooter(false);
    PDF::SetFont('times', '', 16,'','false');
    PDF::AddPage();
    PDF::writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
    $filename = "/invoice-pdf-".date('YmdThis').".pdf";
    $publicPath = base_path("public/images/uploads/pdf/".date('Y')."/".date('m'));
    $assetPath = asset("public/images/uploads/pdf/".date('Y')."/".date('m'));
    File::makeDirectory($publicPath, $mode = 0777, true, true);
    PDF::Output($publicPath.$filename, 'F');
    return $assetPath.$filename;
}

function sendIcalEvent($request, $to_name, $to_address, $body, $service, $customer, $currentUser)
{
    $startTime = $request->input('start_date').' '.$request->input('event_start_time');
    $endTime = $request->input('due_date').' '.$request->input('event_end_time');
    $subject = $to_name.'-'.$service.' '.$body['subject'];        
    $description = $request->input('notes');        
    $location = $customer->customer_address;
    $from_name = $currentUser->first_name;        
    $from_address = 'noreply@bondcrm.com';  
    $domain = 'bondcrm.com';
    $mime_boundary = "----Inspection Booking----".MD5(TIME());
    $headers = "From: ".$from_name." <".$from_address.">\n";
    $headers .= "Reply-To: ".$from_name." <".$from_address.">\n";
    $headers .= "MIME-Version: 1.0\n";
    $headers .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"\n";
    $headers .= "Content-class: urn:content-classes:calendarmessage\n";
    $message = "--$mime_boundary\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\n";
    $message .= "Content-Transfer-Encoding: 8bit\n\n";
    $message .= $body['body'];
    $message .= "--$mime_boundary\r\n";
    $ical = 'BEGIN:VCALENDAR' . "\r\n" .
        'PRODID:-//Microsoft Corporation//Outlook 10.0 MIMEDIR//EN' . "\r\n" .
        'VERSION:2.0' . "\r\n" .
        'METHOD:REQUEST' . "\r\n" .
        'BEGIN:VTIMEZONE' . "\r\n" .
        'TZID:Eastern Time' . "\r\n" .
        'BEGIN:STANDARD' . "\r\n" .
        'DTSTART:20091101T020000' . "\r\n" .
        'RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=1SU;BYMONTH=11' . "\r\n" .
        'TZOFFSETFROM:-0400' . "\r\n" .
        'TZOFFSETTO:-0500' . "\r\n" .
        'TZNAME:EST' . "\r\n" .
        'END:STANDARD' . "\r\n" .
        'BEGIN:DAYLIGHT' . "\r\n" .
        'DTSTART:20090301T020000' . "\r\n" .
        'RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=2SU;BYMONTH=3' . "\r\n" .
        'TZOFFSETFROM:-0500' . "\r\n" .
        'TZOFFSETTO:-0400' . "\r\n" .
        'TZNAME:EDST' . "\r\n" .
        'END:DAYLIGHT' . "\r\n" .
        'END:VTIMEZONE' . "\r\n" .  
        'BEGIN:VEVENT' . "\r\n" .
        'ORGANIZER;CN="'.$from_name.'":MAILTO:'.$from_address. "\r\n" .
        'ATTENDEE;CN="'.$to_name.'";ROLE=REQ-PARTICIPANT;RSVP=TRUE:MAILTO:'.$to_address. "\r\n" .
        'LAST-MODIFIED:' . date("Ymd\TGis") . "\r\n" .
        'UID:'.date("Ymd\TGis", strtotime($startTime)).rand()."@".$domain."\r\n" .
        'DTSTAMP:'.date("Ymd\TGis"). "\r\n" .
        'DTSTART;TZID="Eastern Time":'.date("Ymd\THis", strtotime($startTime)). "\r\n" .
        'DTEND;TZID="Eastern Time":'.date("Ymd\THis", strtotime($endTime)). "\r\n" .
        'TRANSP:OPAQUE'. "\r\n" .
        'SEQUENCE:1'. "\r\n" .
        'SUMMARY:' . $subject . "\r\n" .
        'LOCATION:' . $location . "\r\n" .
        'CLASS:PUBLIC'. "\r\n" .
        'PRIORITY:5'. "\r\n" .
        'BEGIN:VALARM' . "\r\n" .
        'TRIGGER:-PT15M' . "\r\n" .
        'ACTION:DISPLAY' . "\r\n" .
        'DESCRIPTION:Reminder' . "\r\n" .
        'END:VALARM' . "\r\n" .
        'END:VEVENT'. "\r\n" .
        'END:VCALENDAR'. "\r\n";
    $message .= 'Content-Type: text/calendar;name="inspection.ics";method=REQUEST'."\n";
    $message .= "Content-Transfer-Encoding: 8bit\n\n";
    $message .= $ical;
    return mail($to_address, $subject, $message, $headers);
}

function icsInvite($request, $customer){

    $startDate = $request->input('start_date').' '.$request->input('event_start_time');
    $endDates = $request->input('due_date').' '.$request->input('event_end_time');
    $startDate = date('YmdThis', strtotime($startDate));
    $endDates = date('YmdThis', strtotime($endDates));
    $notes = $request->input('notes');
    $event_loc = $customer->customer_address;
    $title = $request->input('title');
    $representative = $request->input('representative');
    return 'https://calendar.google.com/calendar/r/eventedit?text='.$title.'&dates='.$startDate.'/'.$endDates.'&details='.$notes.'&location='.$event_loc.'&pli=1&uid='.$representative.'&sf=true&output=xml&t=AKUaPmZGcOZxPNY_7PMRNOQ9sx56hnKgw7uWh2m_14_BmiHszx-EfHI1rI2UCD0PqRaKOzzoX6ssxF0Jjc6783grvSS32gMXtg%3D%3D';
}

function icsInviteFunction($request)
{   
    $startDate = $request->input('start_date').' '.$request->input('event_start_time');
    $endDates = $request->input('due_date').' '.$request->input('event_end_time');
    $startDate = date('YmdThis', strtotime($startDate));
    $endDates = date('YmdThis', strtotime($endDates));
    $note = $request->input('note');
    $title = $request->input('title');
    $event_loc = $request->input('event_loc');
    $meeting_with = $request->input('meeting_with');
    $link = 'https://calendar.google.com/calendar/r/eventedit?text='.$title.'&dates='.$startDate.'/'.$endDates.'&details='.$note.'&location='.$event_loc.'&pli=1&uid='.$meeting_with.'&sf=true&output=xml&t=AKUaPmZGcOZxPNY_7PMRNOQ9sx56hnKgw7uWh2m_14_BmiHszx-EfHI1rI2UCD0PqRaKOzzoX6ssxF0Jjc6783grvSS32gMXtg%3D%3D';
    return $link;
}

function mailSentToTechnician($currentUser, $request, $task_data, $link_data, $data){

    $link = icsInviteFunction($request);
    $task_data['icsInvite'] = '<a href="'.$link.'" >Click Here </a>';
    $task_data['event_time']= $data['event_time'];
    $task_data['ASSIGNBY']= $currentUser->first_name;

    $customer = new \App\Customer();
    $customer->customer_address = $request->input('event_loc');
    $emailBody = emailTemplate($currentUser, 'inspection_email_template', $task_data);
    $subject = $currentUser->first_name.'-'.$emailBody['subject'].'to'.$task_data['USER_NAME'];

    sendIcalEvent(
        $request, $task_data['USER_NAME'], $task_data['USER_EMAIL'], $emailBody, $subject, $customer, $currentUser
    ); 

    $body = view("EmailTemplate/taskNotificationCutomer",$task_data);
    $subject = $currentUser->first_name.'-'.$emailBody['subject'].'to'.$task_data['USER_NAME'];

    sendIcalEvent(
        $request, $currentUser->first_name, $currentUser->email, $emailBody, $subject, $customer, $currentUser
    );
}
function mailSentToTechnicianAssignJob($currentUser, $request, $task_data, $link_data, $data){

    $link = icsInviteFunction($request);
    $task_data['icsInvite'] = '<a href="'.$link.'" >Click Here </a>';
    $task_data['event_time']= $data['event_time'];
    $task_data['ASSIGNBY']= $currentUser->first_name;

    $customer = new \App\Customer();
    $customer->customer_address = $request->input('event_loc');
    $emailBody = emailTemplate($currentUser, 'scheduler_assign_job_mail', $task_data);
    $subject = $currentUser->first_name.'-'.$emailBody['subject'].'to'.$task_data['USER_NAME'];

    sendIcalEvent(
        $request, $task_data['USER_NAME'], $task_data['USER_EMAIL'], $emailBody, $subject, $customer, $currentUser
    ); 

    $body = view("EmailTemplate/taskNotificationCutomer",$task_data);
    $subject = $currentUser->first_name.'-'.$emailBody['subject'].'to'.$task_data['USER_NAME'];

    sendIcalEvent(
        $request, $currentUser->first_name, $currentUser->email, $emailBody, $subject, $customer, $currentUser
    );
}