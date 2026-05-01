<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

/**
 * Since there is no dedicated 'offers' table, we provide a dynamic-ready 
 * JSON response that can be easily moved to a database table later.
 */

$offers = [
    [
        "id" => 1,
        "title" => "Referral Bonus",
        "desc" => "Earn 500 points when anyone joins via your link - you win!",
        "image" => "https://images.unsplash.com/photo-1529156069898-49953e39b3ac?q=80&w=800&auto=format&fit=crop",
        "action" => "/deals"
    ],
    [
        "id" => 2,
        "title" => "Scratch & Win",
        "desc" => "Get a scratch card on every completed booking - redeem after 3!",
        "image" => "https://images.unsplash.com/photo-1513201099705-a9746e1e201f?q=80&w=800&auto=format&fit=crop",
        "action" => "/rewards"
    ],
    [
        "id" => 3,
        "title" => "First Booking",
        "desc" => "Enjoy ₹100 flat discount on your very first home service booking.",
        "image" => "https://images.unsplash.com/photo-1581578731548-c64695cc6952?q=80&w=800&auto=format&fit=crop",
        "action" => "/bookings"
    ]
];

sendResponse(true, "Offers fetched successfully", $offers);
?>
