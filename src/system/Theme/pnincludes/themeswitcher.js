function showthemeimage() {
    var newTheme = $F('newtheme');
    $('preview').src = $F('previmg_' + newTheme);
    $('preview').title = $('theme_' + newTheme).title;
    $('preview').alt = newTheme;
}