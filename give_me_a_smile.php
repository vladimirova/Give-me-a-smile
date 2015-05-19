<?php
/*
Plugin Name: Give me a smile
Plugin URI: https://github.com/vladimirova/Give-me-a-smile
Description: This plugin provides simple voting plugin for your site.
Author: Nonka Vladimirova
Version: 1.1
Author URI: https://github.com/vladimirova
*/

class Give_me_a_smile extends WP_Widget
{
    function give_me_a_smile()
    {
        $widget_options = array(
            'classname' => 'give_me_a_smile',
            'description' => __('Displays emoticonts for voting')
            );

        parent::WP_Widget('give_me_a_smile', 'Give me a smile', $widget_options);
    }

    function widget($args, $instance)
    {
        extract( $args, EXTR_SKIP);


        $sad = plugins_url('images/sad.png', __FILE__);
        $smile = plugins_url('images/smile.png', __FILE__ );

        $title = 'If you like my site give me a smile!';



        if(isset($_SESSION['vote']))
        {
            global $wpdb;
            $smile_count = $wpdb->get_var("SELECT smile_count FROM ".$wpdb->base_prefix."rating_smiles WHERE id = 1");
            $sad_count = $wpdb->get_var("SELECT sad_count FROM ".$wpdb->base_prefix."rating_smiles WHERE id = 1");

            ?>
            <aside class="widget">
                <h2 class="widget-title"> Thanks for voting </h2>
                <h3> Smiles <?php echo $smile_count; ?></h3>
                <h3> Sad faces <?php echo $sad_count; ?></h3>
            </aside>
            <?php
        }
        else
        {
            ?>

            <aside class="widget">
                <h2 class="widget-title"><?php echo $title ?></h2>
                <a href="?sad=1"><img src="<?php echo $sad ?>" height=”50px” width=”50px”></a>
                <a href="?smile=1"><img src="<?php echo $smile ?>" height=”50px” width=”50px”></a>
            </aside>

            <?php echo $after_widget;?>

            <?php
        }
    }

    function update($new_instance, $old_instance)
    {
        $instance = $old_instance;

        $instance['title'] = strip_tags( $new_instance['title'] );
        return $instance;
    }

    function form($instance)
    {
        $defaults = array( 'title' => 'If you like my site give me a smile!');
        $instance = wp_parse_args( (array) $instance, $defaults );
        ?>

        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php echo 'Title:'; ?></label>
            <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
        </p>

        <?php

    }

    public function setup_plugin()
    {
        global $wpdb;

        if (!empty($wpdb->charset))
        {
            $charset_collate = "DEFAULT CHARACTER SET ".$wpdb->charset;

            $sql = "CREATE TABLE ".$wpdb->base_prefix."rating_smiles (
                        id int(20) PRIMARY KEY NOT NULL,
                        smile_count int(20) DEFAULT '0',
                        sad_count int(20) DEFAULT '0'
            ) ".$charset_collate.";";

            $wpdb->query($sql);

            $sql = "INSERT INTO ".$wpdb->base_prefix."rating_smiles
                    (id, smile_count, sad_count)
                    VALUES(1,0,0)";

            $wpdb->query($wpdb->prepare($sql,1));
        }


    }
}

function give_me_a_smile_init()
{
    if ( !function_exists('register_widget') )
    return;

    register_widget('give_me_a_smile');
}

add_action('widgets_init', 'give_me_a_smile_init');

function register_session(){
    if( !session_id() )
        session_start();
    //session_destroy(); //only for test
}
add_action('init','register_session');

$give_me_a_smile = new Give_me_a_smile();
add_action('init', array(&$give_me_a_smile, 'setup_plugin'), 1);



function sad()
{
    if(isset($_GET['sad']))
    {
        if(isset($_SESSION['vote']))
        {
            return;
        }

        global $wpdb;

        $sql = 'UPDATE '.$wpdb->base_prefix.'rating_smiles
                  SET sad_count = sad_count + 1
                  WHERE id = 1';

        $wpdb->query($wpdb->prepare($sql,1));

        $_SESSION['vote'] = 'true';
    }
}

function smile()
{
    if(isset($_GET['smile']))
    {
        if(isset($_SESSION['vote']))
        {
            return;
        }
        global $wpdb;

        $sql = "UPDATE ".$wpdb->base_prefix."rating_smiles
                SET smile_count = smile_count + 1
                WHERE id = 1";

        $wpdb->query($wpdb->prepare($sql,1));

        $_SESSION['vote'] = 'true';
    }
}

add_action('init', 'sad');
add_action('init', 'smile');
?>
