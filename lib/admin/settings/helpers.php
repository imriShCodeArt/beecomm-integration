<?php
/**
 * Helper callbacks for section descriptions and layout enhancements.
 */

/**
 * Renders the instruction box above the Order Template fields.
 *
 * @param array $args Contains 'description' and 'id'.
 */
function beecomm_order_template_section_callback($args)
{
    ?>
    <style>
        #msg-instructions {
            position: relative;
        }

        .tag-info {
            position: absolute;
            background-color: #f1f1f1;
            border-top: 5px solid #7ad03a;
            border-bottom: 5px solid #7ad03a;
            padding: 10px;
            right: 5%;
            top: -50px;
            max-width: 600px;
            font-size: 13px;
        }
    </style>
    <div id="msg-instructions">
        <p class="tag-info" id="<?php echo esc_attr($args['id']); ?>">
            <?php echo wp_kses_post($args['description']); ?>
        </p>
    </div>
    <?php
}
