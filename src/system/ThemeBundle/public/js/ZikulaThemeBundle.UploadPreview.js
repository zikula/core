(function () {
  // browser feature detection
  const canObjectURL = !!(window.URL && URL.createObjectURL);
  const canFileReader = 'undefined' !== typeof FileReader;

  if ('undefined' === typeof $) {
    function $(id) {
      return document.getElementById(id);
    }
  }

  // initialises one upload preview widget based on a field name (e.g. "foo" -> file-foo, img-foo, …)
  function initImagePreview(fieldName) {
    const input = $(fieldName);
    const img = $(`img-${fieldName}`);
    const figure = $(`figure-${fieldName}`);
    const caption = $(`caption-${fieldName}`);
    const pdfContainer = $(`pdf-${fieldName}`);
    const fallback = $(`fallback-${fieldName}`);
    const fbText = $(`fallback-text-${fieldName}`);
    const status = $(`status-${fieldName}`);

    if (!input || !img || !figure || !caption || !pdfContainer || !fallback || !fbText || !status) {
      // incomplete markup structure - abort
      return;
    }

    function clearPreview() {
      figure.classList.add('d-none');
      fallback.classList.add('d-none');
      img.removeAttribute('src');
      img.alt = '';
      caption.textContent = '';
      pdfContainer.classList.add('d-none');
      fbText.textContent = '';
      status.textContent = '';
    }

    function showFallback(message, file) {
      figure.classList.add('d-none');
      fallback.classList.remove('d-none');
      fbText.textContent = file ? `${message} ${file.name} (${Math.round(file.size / 1024)} KB)` : message;
      status.textContent = 'No image preview available.';
    }

    function showImagePreview(file) {
      img.alt = file.name || 'selected image';

      function onLoadDone(src) {
        img.onerror = function () {
          showFallback('Selected file could not be loaded as image.', file);
        };
        img.onload = function () {
          // blob-URL nach dem Laden freigeben (Memory Leak vermeiden)
          try {
            if (src && src.startsWith('blob:')) {
              URL.revokeObjectURL(src);
            }
          } catch (_) {}
        };
        img.src = src;
        caption.textContent = `${file.name} (${Math.round(file.size / 1024)} KB)`;
        fallback.classList.add('d-none');
        figure.classList.remove('d-none');
        status.textContent = 'Updated image preview.';
      }

      if (canObjectURL) {
        onLoadDone(URL.createObjectURL(file));
      } else if (canFileReader) {
        const reader = new FileReader();
        reader.onload = (e) => onLoadDone(e.target.result);
        reader.onerror = () => showFallback('File could not be read.', file);
        reader.readAsDataURL(file);
      } else {
        showFallback('Your browser does not support file preview.', file);
      }
    }

    // event listener per widget
    input.addEventListener('change', () => {
      clearPreview();

      const file = input.files && input.files[0];
      if (!file) {
        return;
      }

      // allow only real images and pdf files; fallback otherwise
      const isImage = file.type && file.type.startsWith('image/');
      const isPdf = 'application/pdf' === file.type;
      if (!isImage && !isPdf) {
        // showFallback('This is not an image file. No preview available.', file);
        showFallback('', file);
        return;
      }

      // Optional: size limit
      // if (file.size > 5 * 1024 * 1024) {
      //   showFallback('File size is greater than 5 MB. Preview will be skipped.', file);
      //   return;
      // }

      if (isImage) {
        showImagePreview(file);
      } else if (isPdf) {
        pdfContainer.src = URL.createObjectURL(file);
        pdfContainer.classList.remove('d-none');
      }
    });
  }

  // initialize: find all .image-upload[data-fieldname] containers
  function autoInit() {
    const containers = document.querySelectorAll('.image-upload[data-fieldname]');
    containers.forEach((el) => {
      const fieldName = el.getAttribute('data-fieldname');
      if (fieldName) {
        initImagePreview(fieldName);
      }
    });
  }

  document.addEventListener('DOMContentLoaded', autoInit);

  // make public to allow manual initialisation calls as well
  window.initImagePreview = initImagePreview;
})();
