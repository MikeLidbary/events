<?php

namespace App;
use GuzzleHttp\Client as GuzClient;

use Illuminate\Database\Eloquent\Model;
use Twilio\Rest\Client;

class Event extends Model
{

   /**
    * Fetch events from Eventbrite
    * @param $location
    * @param $date
    * @param $within_radius
    * @param $limit
    * @param $sort_by 
    */
   public function fetchEvents($location,$date,$limit=3,$sort_by="best",$within_radius="10km"){
       $client = new GuzClient();
       $events="";
       $base_url         = "https://www.eventbriteapi.com/v3/events/search?";
       $location         = "location.address=".$location."&location.within=".$within_radius."&expand=venue";
       $eventbrite_token = "&token=".getenv('EVENTBRITE_AUTH_TOKEN');
       $date             = "&start_date.keyword=".$date;
       $sort_by          = "&sort_by=".$sort_by;
       $url              = $base_url.$location.$eventbrite_token.$date.$sort_by;
       $client           = new GuzClient();
       $res              = $client->get($url);
       $data             = json_decode($res->getBody()->getContents());
       // trasform response into a format that can easily be consumed through SMS.
       $count=1;
       foreach ($data->events as $datum){
          $title=$datum->name->text;
          $start_time=explode("T",$datum->start->local);
          $start_date=$start_time[0];
          $start_time=isset($start_time[1]) ? date('h:i a', strtotime($start_time[1])):"";
          $end_time=explode("T",$datum->end->local);
          $end_date=$end_time[0];
          $end_time=isset($end_time[1]) ? date('h:i a', strtotime($end_time[1])):"";
          $price=($datum->is_free==true)?"Free":"Paid";
          $url=$datum->url;
          $venue=$datum->venue->address->address_1;
          $sub_venue=isset($datum->venue->address->address_2)?"(".$datum->venue->address->address_2.")":"";
          $main_body="Event Name:".$title." \n Starts :".$start_date." ".$start_time." \n Ends :".$end_date." ".$end_time." \n Price :".$price." \n Venue :".$venue.$sub_venue." \n Ticket Link :".$url." \n \n ";
          $events.=$main_body;
          if($count==$limit){
             break;
          }
          $count++;
       }
       return $events;
   }

  /**
   * send Twilio SMS
   *
   * @param $to
   * @param $events
   */
  function sendSMS( $to, $events ) {
    // Your Account SID and Auth Token from twilio.com/console
      $sid    = getenv( 'TWILIO_SID' );
      $token  = getenv( 'TWILIO_AUTH_TOKEN' );
      $client = new Client( $sid, $token );
      $client->messages->create(
         $to,
         [
            // A Twilio phone number you purchased at twilio.com/console
            'from' => getenv( 'TWILIO_PHONE_NUMBER' ),
            // the body of the text message you'd like to send
            'body' => $events
         ]
      );
   }

   /**
    * The Event App
    */
    public function eventApp(){
      // replace this your subscribers data
      $subscribers=
      [
          [
          'to' => '+2547********',
          'location' => 'Nairobi',
          ],
          [
            'to' => '+2547********',
            'location' => 'Kisumu'
         ]
      ];
      foreach($subscribers as $subscriber){
         $events=$this->fetchEvents($subscriber['location'],"this_week");
          // send 
         if($events!=""){
            $this->sendSMS($subscriber['to'],$events);
         }
      }
  }

 

}
