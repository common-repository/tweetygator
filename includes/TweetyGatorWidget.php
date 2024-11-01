<?php
class TweetyGatorWidget extends WP_Widget
{
    const ORDER_NEWESTFIRST = 'DESC';
    const ORDER_NEWESTLAST = 'ASC';

    function TweetyGatorWidget() {
        $widget_ops = array(
            'classname' => 'TweetyGatorWidget',
            'description' => __( 'Shows an aggregation of tweets')
        );
        $this->WP_Widget('TweetyGatorWidget', __('Twitter aggregator'), $widget_ops);
    }

    function form($instance) {
        // Default arguments
        $instance = wp_parse_args( (array) $instance, array(
            'orderDirection' => self::ORDER_NEWESTFIRST,
            'list' => '',
            'amountShown'   => '5'
        ));
        $followList = esc_attr( $instance['list'] );
        $amountShown = esc_attr( $instance['amountShown'] );

?>
        <p>
            <label for="<?php echo $this->get_field_id('orderDirection'); ?>"><?php _e( 'Sort by:' ); ?></label>
            <select name="<?php echo $this->get_field_name('orderDirection'); ?>" id="<?php echo $this->get_field_id('orderDirection'); ?>" class="widefat">
                    <option value="<?php echo TweetyGatorWidget::ORDER_NEWESTFIRST ?>"<?php selected( $instance['orderDirection'], TweetyGatorWidget::ORDER_NEWESTFIRST ); ?>><?php _e('Newest first'); ?></option>
                    <option value="<?php echo TweetyGatorWidget::ORDER_NEWESTLAST ?>"<?php selected( $instance['orderDirection'], TweetyGatorWidget::ORDER_NEWESTLAST ); ?>><?php _e('Newest last'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('amountShown'); ?>"><?php _e('Amount of visible tweets:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('amountShown'); ?>" name="<?php echo $this->get_field_name('amountShown'); ?>" type="text" value="<?php echo $amountShown; ?>" />
            <br />
            <small><?php _e( 'Amount of tweets shown in the widget (max 20)' ); ?></small>
        </p>
<?php
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['list'] = strip_tags($new_instance['list']);
        $instance['amountShown'] = strip_tags($new_instance['amountShown']);
        if (!is_numeric($instance['amountShown'])) {
            $instance['amountShown'] = 5;
        } else {
            if (0 >= (int) $instance['amountShown'] || 20 < (int) $instance['amountShown']) {
                $instance['amountShown'] = 5;
            }
        }

        if ( in_array( $new_instance['orderDirection'], array( self::ORDER_NEWESTFIRST, self::ORDER_NEWESTLAST ) ) ) {
            $instance['orderDirection'] = $new_instance['orderDirection'];
        } else {
            $instance['orderDirection'] = self::ORDER_NEWESTFIRST;
        }

        // when saving the data, we remove the cache
        $tweetyGator = new TweetyGator();
        $tweetyGator->cleanCache();

        return $instance;
    }

    function widget($args, $instance) {
        extract($instance);
        $tweetyGator = new TweetyGator();

        $options = get_option('tweetygator_options');

        $twitterData = $tweetyGator->fetchTweets($options);
        $output = $tweetyGator->convertTwitterResultToList($twitterData, $instance);

        echo $output;
    }

}