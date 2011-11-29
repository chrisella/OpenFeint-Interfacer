<?php

/**
 * Description of OFInterfacer
 *
 * @author Chris Ella
 */

include 'OFInterfacerConfig.php';

/*
 * A class representing a single highscore
 */
class OFHighscore {
    var $score = 0;
    var $created = null;
    var $updated = null;
    var $displayText = null;
    var $userName = null;
    var $userProfilePic = null;
    var $userGamerScore = null;
}

/*
 * A class representing a Leaderboard
 */
class OFLeaderboard {
    var $name = null;
    var $id = null;
    var $size = null;
    var $highscores = array();
}

/*
 * The actual Interfacer main class.
 * Instantiate and call retrieveLatestData() to begin.
 */
class OFInterfacer {
    var $gameProfile = null;
    var $gameName = null;
    var $gameVersion = null;
    var $leaderboards = array();
    
    
    
    /*
     * Retrieves the latest data from the OpenFeint servers.
     * This should be called explicitly when the object is created.
     * May end up moving this to the constructor so explicit calling isn't needed.
     */
    public function processLatestData()
    {
        $data = $this->downloadData();
        $this->processData( $data );
    }
    
    /*
     * Checks all Cached files and updates if necessary
     * Keep in mind that only the top 30 highscores are cached.
     * If other pages are requested then it will currently fail until fetching 
     * of uncached pages from the server is implemented.
     */
    public function checkAllCache()
    {
        global $OFIconfig;
        
        echo "Caching Results<br/><hr/>";
        
        // GameProfile (123456.xml)
        $this->cacheXML("http://api.openfeint.com/api/games/".$OFIconfig['OF_GAME_ID'].".xml".$OFIconfig['OF_AUTH_URL_EXT'], $OFIconfig['OF_GAME_ID'].".xml");
        // Leaderboards (Leaderboards.xml)
        $this->cacheXML("http://api.openfeint.com/api/games/".$OFIconfig['OF_GAME_ID']."/leaderboards.xml".$OFIconfig['OF_AUTH_URL_EXT'], "leaderboards.xml");
        // HighScores (leaderboards/123456/highscores.xml)
        $leaderboardsxml = simplexml_load_file( $OFIconfig['CACHE_DIR']."/leaderboards.xml" );
        for($is = 0; $is < count($leaderboardsxml->leaderboard); ++$is)
        {
            $leaderboardxml = &$leaderboardsxml->leaderboard[$is];
            $leaderboardid = (int)$leaderboardxml->id;
            $leaderboardurl = $leaderboardxml->highscores_url->__toString();
            mkdir($OFIconfig['CACHE_DIR']."/leaderboards/".$leaderboardid, 0777, true);
            $this->cacheXML( $leaderboardurl.$OFIconfig['OF_AUTH_URL_EXT'], "leaderboards/".$leaderboardid."/highscores.xml");
        }
    }
    
    /*
     * Checks if current local copy of xml is out of date (default: 15 mins)
     * Updates the local cache if neccessary.
     */
    private function cacheXML( $file, $filename )
    {
        global $OFIconfig;
        
        // If there is no cached version of gameProfile then download by default
        if(!file_exists($OFIconfig['CACHE_DIR']."/".$filename))
        {
            $this->copyToCache( $file, $OFIconfig['CACHE_DIR'].$filename );
            return;
        } else if($OFIconfig['CACHE_UPDATE_TIME'] 
                <= 
                (time() - filemtime($OFIconfig['CACHE_DIR'].$filename)))
        {        
            $this->copyToCache( $file, $OFIconfig['CACHE_DIR'].$filename );
            return;
        }
        echo "<br/>No need to update: " . $filename;
    }
    
    /*
     * Copies the sourceFile to the cache
     */
    private function copyToCache( $sourceFile, $destFile )
    {
        $xml = simplexml_load_file($sourceFile);
        $xml->asXML($destFile);
        echo "<br/>Copied Successfully: " . $destFile;
    }
    
    /*
     * Loads the latest GameProfile from the cache
     * @return The latest Game Profile data, should be passed to processData
     */
    private function downloadData()
    {
        global $OFIconfig;
        $url = $OFIconfig['CACHE_DIR'].$OFIconfig['OF_GAME_ID'].".xml";
        $xml = simplexml_load_file($url);
        return $xml;
    }
    
    /*
     * Processes the data retrieved from the OpenFeint servers.
     */
    private function processData( $data )
    {
        global $OFIconfig;
        if( isset($data) )
        {
            // Store basic game info
            $this->gameProfile = $data;
            $this->gameName = $data->name->__toString();
            $this->gameVersion = $data->current_version->__toString();
            
            // Iterate leaderboards and download/process each
            $leaderboardsxml = simplexml_load_file( $OFIconfig['CACHE_DIR']."leaderboards.xml" );
            
            for($is = 0; $is < count($leaderboardsxml->leaderboard); ++$is)
            {
                $leaderboardxml = &$leaderboardsxml->leaderboard[$is];
                
                $leaderboard = new OFLeaderboard();
                $leaderboard->name = $leaderboardxml->name->__toString();
                $leaderboard->id = $leaderboardxml->id->__toString();
                $leaderboard->size = (int)$leaderboardxml->size;
                
                $highscoresxml = simplexml_load_file( $OFIconfig['CACHE_DIR']."leaderboards/".$leaderboard->id."highscores.xml" );
                
                // Iterate Highscores and add
                for( $hs = 0; $hs < count($highscoresxml->highscore); ++$hs )
                {
                    $highscorexml = &$highscoresxml->highscore[$hs];
                    
                    $highscore = new OFHighscore();
                    $highscore->score = (int)$highscoresxml->score;
                    $highscore->created = $highscoresxml->created_at->__toString();
                    $highscore->updated = $highscoresxml->updated_at->__toString();
                    $highscore->displayText = $highscoresxml->display_text->__toString();
                    $highscore->userName = $highscoresxml->user->name->__toString();
                    $highscore->userProfilePic = $highscoresxml->user->profile_picture_url->__toString();
                    $highscore->userGamerScore = (int)$highscoresxml->user->open_feint_gamer_score;
                    
                    $leaderboard->highscores[] = $highscore;
                }
                
                $this->leaderboards[] = $leaderboard;
                
            }
        }
    }
    
}

?>
