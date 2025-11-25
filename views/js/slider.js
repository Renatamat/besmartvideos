(function () {
  const mobileQuery = window.matchMedia('(max-width: 767px)');

  function setVideoSource(video) {
    if (!video) {
      return;
    }

    const targetSrc = mobileQuery.matches ? video.dataset.mobileSrc : video.dataset.desktopSrc;

    if (video.dataset.currentSrc === targetSrc) {
      return;
    }

    video.pause();
    if (targetSrc) {
      video.src = targetSrc;
      video.load();
    }
    video.dataset.currentSrc = targetSrc || '';
  }

  function playVideo(video) {
    if (!video) {
      return;
    }

    video.muted = true;
    video.setAttribute('playsinline', 'true');
    const playPromise = video.play();
    if (playPromise && typeof playPromise.catch === 'function') {
      playPromise.catch(function () {});
    }
  }

  function pauseVideos(container) {
    container.querySelectorAll('video').forEach(function (video) {
      video.pause();
    });
  }

  function debounce(fn, wait) {
    let timeout;
    return function () {
      clearTimeout(timeout);
      timeout = setTimeout(fn, wait);
    };
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.besmartvideoslider').forEach(function (container) {
      const swiperElement = container.querySelector('.js-besmartvideoslider-swiper');
      if (!swiperElement || typeof Swiper === 'undefined') {
        return;
      }

      const updateSources = function () {
        swiperElement.querySelectorAll('video').forEach(function (video) {
          setVideoSource(video);
        });
      };

      const swiper = new Swiper(swiperElement, {
        loop: true,
        pagination: {
          el: container.querySelector('.swiper-pagination'),
          clickable: true,
        },
        navigation: {
          nextEl: container.querySelector('.swiper-button-next'),
          prevEl: container.querySelector('.swiper-button-prev'),
        },
        on: {
          init: function () {
            updateSources();
            const activeVideo = swiperElement.querySelector('.swiper-slide-active video');
            setVideoSource(activeVideo);
            playVideo(activeVideo);
          },
          slideChange: function () {
            pauseVideos(swiperElement);
            const activeVideo = swiperElement.querySelector('.swiper-slide-active video');
            setVideoSource(activeVideo);
            playVideo(activeVideo);
          },
        },
      });

      const refreshWithDebounce = debounce(function () {
        updateSources();
        const activeVideo = swiperElement.querySelector('.swiper-slide-active video');
        setVideoSource(activeVideo);
        playVideo(activeVideo);
      }, 200);

      mobileQuery.addEventListener('change', refreshWithDebounce);
      window.addEventListener('resize', refreshWithDebounce);
    });
  });
})();
