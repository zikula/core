document.addEventListener('DOMContentLoaded', () => {
  // open download links in new tab
  document.querySelectorAll('.ea-vich-file-name').forEach(link => {
    if ('a' === link.tagName.toLowerCase()) {
      link.setAttribute('target', '_blank');
      link.setAttribute('rel', 'noopener noreferrer');
    }
  });
});
