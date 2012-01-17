<?php
echo Asset::css('gallery.css');
echo '<div class="gallery">';
echo html_entity_decode($gallery['breadcrumb'], ENT_QUOTES, 'UTF-8');
echo html_entity_decode($gallery['data'], ENT_QUOTES, 'UTF-8');
echo '</div>';
?>