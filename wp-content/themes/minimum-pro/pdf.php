<?php if ( get_post_mime_type($post->ID) == 'application/pdf' ) : ?>
    <object data="<?php echo wp_get_attachment_url($post->ID); ?>" type="application/pdf" width="100%" height="1000px"><a href="<?php echo wp_get_attachment_url($post->ID); ?>">Download the PDF here.</a></object>
<?php endif; ?>