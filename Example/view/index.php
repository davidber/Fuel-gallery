<?php
echo Asset::css('gallery.css');
echo '<div class="gallery">';
echo htmlspecialchars_decode($gallery['breadcrumb']);
echo htmlspecialchars_decode($gallery['data']);
echo '</div>';
?>