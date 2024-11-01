<?php
class TweetyGator
{
    const FIELD_USERS = 'users';
    const FIELD_HASHES = 'hashes';
    const FIELD_KEYWORDS = 'keywords';
    const FIELD_CACHE = 'cache';

    const SECTION_MAIN = 'main';
    const SECTION_MISC = 'misc';

    protected $_cacheKey = 'TweetyGatorData';

    /**
     * Initialize the widget
     *
     * @return mixed
     */
    public function initWidget ()
    {
        return register_widget("TweetyGatorWidget");
    }

    /**
     * Build the admin menu for configuring the plugin
     */
    public function adminMenu ()
    {
        add_options_page('TweetyGator Settings', 'TweetyGator Settings', 'manage_options', 'tweetygator', 'tweetygator_show_settings_page');
    }

    /**
     * Shows the settings page
     */
    public function showSettingsPage ()
    {
        $template = new TweetyGatorTemplate();
        $output = $template->render(__DIR__ . '/system_templates/settings.phtml');
        echo $output;
    }

    /**
     * Initialize the options for the settings page
     */
    public function initAdmin ()
    {
        register_setting( 'tweetygator_options', 'tweetygator_options', 'tweetygator_options_validate');
        add_settings_section('tweetygator_' . self::SECTION_MAIN, 'Twitter settings', 'tweetygator_section_text', 'tweetygator');
        add_settings_section('tweetygator_' . self::SECTION_MISC, 'Other settings', 'tweetygator_section_' . self::SECTION_MISC . '_text', 'tweetygator');

        /*
         * Fields we need are:
         *
         * Users - the users you wish to 'follow'
         * Hashes - the hashtags you wish to 'follow'
         * Keywords - any keywords you wish to 'follow'
         */
        add_settings_field('tweetygator_users', 'Tweeps to follow', 'tweetygator_show_users_field', 'tweetygator', 'tweetygator_' . self::SECTION_MAIN);
        add_settings_field('tweetygator_hashes', 'Hashtags', 'tweetygator_show_hashes_field', 'tweetygator', 'tweetygator_' . self::SECTION_MAIN);
        add_settings_field('tweetygator_keywords', 'Keywords', 'tweetygator_show_keywords_field', 'tweetygator', 'tweetygator_' . self::SECTION_MAIN);

        add_settings_field('tweetygator_cache', 'Cache TTL', 'tweetygator_show_cache_field', 'tweetygator', 'tweetygator_' . self::SECTION_MISC);
    }

    /**
     * Show the main section of the settings
     */
    public function showSettingsSection ($section)
    {
        $template = new TweetyGatorTemplate();
        $location = '';
        switch ($section) {
            case self::SECTION_MAIN:
            case self::SECTION_MISC:
                $location = __DIR__ . '/system_templates/settings_section' . $section . '.phtml';
                break;
        }
        $output = $template->render($location);
        echo $output;
    }

    /**
     * Validate the incoming options from the admin panel
     *
     * @param array $input 
     * @return array
     */
    public function validateOptions (array $input = array())
    {
        $newInput = array();
        $newInput['tweetygator_users'] = strip_tags($input['tweetygator_users']);
        $newInput['tweetygator_hashes'] = strip_tags($input['tweetygator_hashes']);
        $newInput['tweetygator_keywords'] = strip_tags($input['tweetygator_keywords']);

        $newInput['tweetygator_cache'] = (int) strip_tags($input['tweetygator_cache']);

        // cleanup cache
        $this->cleanCache();

        return $newInput;
    }

    /**
     * Show an input field
     *
     * @param string $field
     */
    public function showField ($field)
    {
        $options = get_option('tweetygator_options');
        $template = new TweetyGatorTemplate();
        $location = '';
        switch ($field) {
            case self::FIELD_USERS:
            case self::FIELD_HASHES:
            case self::FIELD_KEYWORDS:
            case self::FIELD_CACHE:
                $template->values = $options['tweetygator_' . $field];
                $location = __DIR__ . '/system_templates/field_' . $field . '.phtml';
                break;
        }
        $output = $template->render($location);
        echo $output;
    }

    /**
     * Removes the cache
     */
    public function cleanCache ()
    {
        delete_transient($this->_cacheKey);

    }

    /**
     * Fetches feeds from all registered twitternames
     */
    public function fetchTweets ($options)
    {
        $data = get_transient($this->_cacheKey);

        if (false === $data) {
            $searchQuery = $this->_listToQuery ($options);

            $url = 'http://search.twitter.com/search.json?q=' . urlencode($searchQuery);
            $content = file_get_contents($url);
            $data = json_decode($content);

            $cacheTTL = (int) $options['tweetygator_cache'];
            if (empty($cacheTTL)) {
                $cacheTTL = 20;
            }

            set_transient($this->_cacheKey, $data, $cacheTTL);
        }

        return $data;
    }

    /**
     * Converts an array of twitter data to a string
     *
     * @param array $data
     * @return string
     */
    public function convertTwitterResultToList ($data, $arrPrefs)
    {
        if (!isset($data->results)) {
            return '';
        }

        $entries = $data->results;
        $entries = array_slice($entries, 0, $arrPrefs['amountShown']);

        if (TweetyGatorWidget::ORDER_NEWESTLAST === $arrPrefs['orderDirection']) {
            $entries = array_reverse($entries);
        }

        $template = new TweetyGatorTemplate();
        $template->entries = $entries;
        $output = $template->render(__DIR__ . '/templates/widget.phtml');

        $container = new TweetyGatorTemplate();
        $container->widget = $output;
        $completeOutput = $container->render(__DIR__ . '/system_templates/widget.phtml');

        return $completeOutput;
    }

    /**
     * Converts a comma separated list of twitter usernames
     * to a search query for the API
     *
     * @param string $list
     * @return string
     */
    protected function _listToQuery ($options)
    {
        $users = $options['tweetygator_users'];
        $hashes = $options['tweetygator_hashes'];
        $keywords = $options['tweetygator_keywords'];

        // USERS
        $arrUsers = explode(',', $users);
        $arrUsers = array_map('trim', $arrUsers);
        array_walk($arrUsers, array($this, '_stripAt'));
        array_walk($arrUsers, array($this, '_addFrom'));

        // HASHTAGS
        $arrHashes = explode(',', $hashes);
        $arrHashes = array_map('trim', $arrHashes);
        array_walk($arrHashes, array($this, '_addHash'));

        // KEYWORDS
        $arrKeywords = explode(',', $keywords);
        $arrKeywords = array_map('trim', $arrKeywords);

        $arrList = array_merge($arrUsers, $arrHashes, $arrKeywords);

        $query = implode(' OR ', $arrList);

        return $query;
    }

    /**
     * Strips the @ from a username if necessary
     *
     * @param string $item
     */
    protected function _stripAt (&$item)
    {
        if (strpos($item, '@') === 0) {
            $item = substr($item, 1);
        }
    }

    /**
     * Adds data to the string for the search query
     *
     * @param string $item
     */
    protected function _addFrom (&$item)
    {
        $item = sprintf('from:%s', $item);
    }

    /**
     * Adds a hash to the item if necessary
     *
     * @param string $item
     */
    protected function _addHash (&$item)
    {
        if (strpos($item, '#') !== 0) {
            $item = sprintf('#%s', $item);
        }
    }
}