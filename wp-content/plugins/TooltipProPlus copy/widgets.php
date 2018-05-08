<?php

class CMTT_RandomTerms_Widget extends WP_Widget
{

    public static function init()
    {
        add_action('widgets_init', create_function('', 'return register_widget("' . get_class() . '");'));
    }

    /**
     * Create widget
     */
    public function CMTT_RandomTerms_Widget()
    {
        $widget_ops = array('classname' => 'CMTT_RandomTerms_Widget', 'description' => 'Show random glossary terms');
        parent::__construct('CMTT_RandomTerms_Widget', 'Glossary Random Terms', $widget_ops);
    }

    /**
     * Widget options form
     * @param WP_Widget $instance
     */
    public function form($instance)
    {
        $instance = wp_parse_args((array) $instance, array('title' => '', 'count' => 5, 'glink' => '', 'slink' => 'yes'));
        $title = $instance['title'];
        $count = $instance['count'];
        $glink = $instance['glink'];
        $slink = $instance['slink'];
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
        <p><label for="<?php echo $this->get_field_id('count'); ?>">Number of Terms: <input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo esc_attr($count); ?>" /></label></p>
        <p><label for="<?php echo $this->get_field_id('glink'); ?>">Glossary Link Title: <input class="widefat" id="<?php echo $this->get_field_id('glink'); ?>" name="<?php echo $this->get_field_name('glink'); ?>" type="text" value="<?php echo esc_attr($glink); ?>" /></label></p>
        <p><label for="<?php echo $this->get_field_id('slink'); ?>">Show Tooltip for Terms:</br>
                <input id="<?php echo $this->get_field_id('slink'); ?>" name="<?php echo $this->get_field_name('slink'); ?>" type="radio" <?php if( $slink == 'yes' ) echo 'checked="checked"';
        ?> value="yes" /> Yes</br>
                <input id="<?php echo $this->get_field_id('slink'); ?>" name="<?php echo $this->get_field_name('slink'); ?>" type="radio" <?php if( $slink == 'no' ) echo 'checked="checked"';
        ?> value="no" />  No</br>
            </label></p>
        <?php
    }

    /**
     * Update widget options
     * @param WP_Widget $new_instance
     * @param WP_Widget $old_instance
     * @return WP_Widget
     */
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        $instance['count'] = $new_instance['count'];
        $instance['glink'] = $new_instance['glink'];
        $instance['slink'] = $new_instance['slink'];
        return $instance;
    }

    /**
     * Render widget
     *
     * @param array $args
     * @param WP_Widget $instance
     */
    public function widget($args, $instance)
    {
        extract($args, EXTR_SKIP);

        echo $before_widget;
        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);

        if( !empty($title) )
        {
            echo $before_title . $title . $after_title;
        }


        // WIDGET CODE GOES HERE
        $queryArgs = array(
            'post_type'      => 'glossary',
            'post_status'    => 'publish',
            'posts_per_page' => $instance['count'] > 0 ? $instance['count'] : 5,
            'orderby'        => 'rand'
        );
        $query = new WP_Query($queryArgs);
        echo '<ul class="glossary_randomterms_widget">';
        foreach($query->get_posts() as $term)
        {
            $tooltipPart = '';

            /*
             * Check if we display tooltip at all
             */
            $slink = $instance['slink'];
            if( $slink == 'yes' )
            {

                /*
                 * In case we do where we take the content from
                 */
                if( get_option('cmtt_glossaryTooltip') == 1 )
                {

                    if( get_option('cmtt_glossaryExcerptHover') && $term->post_excerpt )
                    {
                        $glossaryItemContent = $term->post_excerpt;
                    }
                    else
                    {
                        $glossaryItemContent = $term->post_content;
                    }
                    $glossaryItemContent = CMTT_Pro::cmtt_glossary_filterTooltipContent($glossaryItemContent, get_permalink($term->ID));
                    if( get_option('cmtt_glossary_addSynonymsTooltip') == 1 )
                    {
                        $synonyms = CMTT_Synonyms::getSynonyms($glossary_item->ID);
                        if( !empty($synonyms) )
                        {
                            $glossaryItemContent.=esc_attr('<br /><strong>' . get_option('cmtt_glossary_addSynonymsTitle') . '</strong> ' . implode(', ', $synonyms));
                        }
                    }
                    $tooltipPart = ' data-tooltip="' . $glossaryItemContent . '"';
                }
                echo '<li><a href="' . get_permalink($term->ID) . '" class="glossaryLink"' . $tooltipPart . '>' . $term->post_title . '</a></li>';
            }
            else
            {
                /*
                 * We do not display tooltip just link to term
                 */
                echo '<li><a href="' . get_permalink($term->ID) . '" >' . $term->post_title . '</a></li>';
            }
        }

        $glink = $instance['glink'];
        $mainPageId = get_option('cmtt_glossaryID');

        if( !empty($glink) && $mainPageId > 0 ) echo '<li><a href="' . get_permalink($mainPageId) . '">' . $glink . '</a></li>';

        echo '</ul>';
        echo $after_widget;
    }

}

class CMTT_Search_Widget extends WP_Widget
{

    public static function init()
    {
        add_action('widgets_init', create_function('', 'return register_widget("' . get_class() . '");'));
    }

    /**
     * Create widget
     */
    public function __construct()
    {
        $widget_ops = array('classname' => 'CMTT_Search_Widget', 'description' => 'Show search box for glossary term items');
        parent::__construct('CMTT_Search_Widget', 'Glossary Search Widget', $widget_ops);
    }

    /**
     * Widget options form
     * @param WP_Widget $instance
     */
    public function form($instance)
    {
        $instance = wp_parse_args((array) $instance, array('title' => ''));
        $title = $instance['title'];
        $label = $instance['label'];
        $buttonlabel = $instance['buttonlabel'];
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">
                Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
            </label>
            <label for="<?php echo $this->get_field_id('label'); ?>">
                Search label: <input class="widefat" id="<?php echo $this->get_field_id('label'); ?>" name="<?php echo $this->get_field_name('label'); ?>" type="text" value="<?php echo esc_attr($label); ?>" />
            </label>
            <label for="<?php echo $this->get_field_id('buttonlabel'); ?>">
                Button label: <input class="widefat" id="<?php echo $this->get_field_id('buttonlabel'); ?>" name="<?php echo $this->get_field_name('buttonlabel'); ?>" type="text" value="<?php echo esc_attr($buttonlabel); ?>" />
            </label>
        </p>
        <?php
    }

    /**
     * Update widget options
     * @param WP_Widget $new_instance
     * @param WP_Widget $old_instance
     * @return WP_Widget
     */
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        $instance['label'] = $new_instance['label'];
        $instance['buttonlabel'] = $new_instance['buttonlabel'];
        return $instance;
    }

    /**
     * Render widget
     *
     * @param array $args
     * @param WP_Widget $instance
     */
    public function widget($args, $instance)
    {
        extract($args, EXTR_SKIP);

        echo $before_widget;

        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
        $searchLabel = empty($instance['label']) ? CMTT_Pro::__('Search') : $instance['label'];
        $searchButtonLabel = empty($instance['buttonlabel']) ? CMTT_Pro::__('Search') : $instance['buttonlabel'];

        $mainPageId = CMTT_Glossary_Index::getGlossaryIndexPageId();
        $mainPageLink = get_permalink($mainPageId);
        $searchTerm = (string) filter_input(INPUT_POST, 'search_term');

        if( !empty($title) )
        {
            echo $before_title . $title . $after_title;
        }
        ?>
        <div class="glossary_search_widget">
            <form action="<?php echo $mainPageLink ?>" method="post">
                <span><?php echo $searchLabel ?></span>
                <input value="<?php echo $searchTerm ?>" class="glossary-widget-search-term" name="search_term" id="glossary-widget-search-term" />
                <input type="submit" value="<?php echo $searchButtonLabel ?>" id="glossary-search" class="glossary-search" />
            </form>
        </div>
        <?php
        echo $after_widget;
    }

}