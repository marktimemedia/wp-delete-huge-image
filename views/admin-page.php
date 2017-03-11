<div class="wrap">
    <h2>Delete Original Images</h2>
    <?php if (!empty($message)): ?>
        <p class="error" style="margin-top:10px;">
        <?php echo $message; ?>
        </p>
    <?php endif; ?>
    
    <? if (count( get_settings_errors()) > 0) : ?>
    <div class="error">
    <? 
        foreach( get_settings_errors() as $errorArr) {
            echo $errorArr['message'] 
//                    .' ('.$errorArr['setting'].')'
                    .'<br>'."\n";
        };
    ?>
    </div>
    <? endif; ?>
        
    <form method="post" action="options.php" enctype="multipart/form-data">
        <? do_settings_sections( 'doi_admin_page' ); ?>
        <? settings_fields( 'doi_options_group' ); //what does this shit do? ?>
        <? submit_button(); ?>
    </form>

    <form method="post" action="#" id="delete-all-originals" >
        <input type="hidden" name="delete-all-original-images" value="delete" />
        <? submit_button('Delete All Original Images'); ?>
    </form>

    <script type="text/javascript" >
        jQuery("form#delete-all-originals").on('submit', function(e) {
            if (confirm("This option will delete all your original uploaded images and can't be undo, but it will not brake anything. Continue?")) {
//                jQuery("form#delete-all-originals").submit();
                return true;
            } else {
                e.preventDefault();
                return false;  
            };
        });
    </script>
</div>