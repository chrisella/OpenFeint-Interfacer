<?php

/*
 * Set the following values as necessary
 */

    global $OFIconfig;

    $OFIconfig['OF_GAME_ID'] = "397163";
    $OFIconfig['OF_PRODUCT_ID'] = "Q3O1A8FYgI31udrKK2XwZw";
    $OFIconfig['OF_SECRET_ID'] = "lVn7wLh7Qs0lZyvFBn3swbhcHgjTQ92p7DgqMAmBIQ";
    $OFIconfig['OF_AUTH_URL_EXT'] = "?client_id=".$OFIconfig['OF_PRODUCT_ID']."&client_secret=".$OFIconfig['OF_SECRET_ID'];
    
    /*
     * Only edit values below this line if you know what you are doing!
     */
    
    $OFIconfig['CACHE_DIR'] = "./xmlcache/";
    $OFIconfig['CACHE_UPDATE_TIME'] = 15 * 60 * 1000;   // milliseconds before local cached data is out of date (default: 15 mins)


?>
