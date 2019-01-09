<?php
/*
 * test_message_decoder.php
 *
 * @(#) $Header: /home/mlemos/cvsroot/mimeparser/test_message_decoder.php,v 1.7 2008/01/08 02:02:32 mlemos Exp $
 *
 */
   include('./config.php');

   require_once('../devlib/mailing/rfc822_addresses.php');
   require_once('../devlib/mailing/mime_parser.php');

   #$message_file=((IsSet($_SERVER['argv']) && count($_SERVER['argv'])>1) ? $_SERVER['argv'][1] : 'test/sample/message.eml');
   
   #$message_file = "./mailtest3.msg";
   $k = 0;
   $message_file = file_get_contents("./test/mail/und/returned.txt");
   $regex = '/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/si';
   preg_match_all($regex, $message_file, $match);
   $do_not_match = array('deq.virginia.gov', 'cntrldeqnet.deq');
   if (count($match) > 0) {
      foreach ($match[0] as $thismatch) {
         if (!strpos($thismatch, $do_not_match[0])) {
            print($thismatch . "<br>");
         }
         print("Found bad email: $thismatch <br>");
         $listobject->querystring = " update facilities set bad_email = 1 where email = '$thismatch' ";
         if ($debug) {
            print($listobject->querystring . " ; <br>");
         }
         $k++;
         $listobject->performQuery();
         
      }
   }
   
   print("<hr>Processed $k return addresses.<br>");
   
/* for testing regexp options
   $regexps = array('/Delivery to the following recipients failed.\n\n(.*?)@(.*?)\n/si', '/To: (.*?)\n/si', '/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/si'
   );
    
   foreach ($regexps as $regex) {
   
      print("<hr>");
      #$message_file = "../mail/und/mailtest3.msg";
      preg_match_all($regex, $message_file, $match);
      if (count($match) > 0) {
         print_r($match);
         
         $userid = $match[1];
         print("Found Email Address ID: " . $userid . " ... ");
         print("Flagging as bad in EDWrD database<br>");
         # just have to update the data record, and flag it as problematic, then we can move on to the next one, 
         # since no need to parse the email message since we already know the facility id
         $listobject->querystring = " update facilities set bad_email = 1 where userid = '$userid' ";
         #if ($debug) {
            print($listobject->querystring . " ; <br>");
         #}
         #$listobject->performQuery();
         
      }

   }
*/
?>