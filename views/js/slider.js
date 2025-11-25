(function () {
  const mobileQuery = window.matchMedia('(max-width: 767px)');

  function setVideoPoster(video) {
    if (!video) {
      return;
    }

    const targetSrc = mobileQuery.matches ? video.dataset.mobileSrc : video.dataset.desktopSrc;
    const targetPoster = mobileQuery.matches ? video.dataset.mobilePoster : video.dataset.desktopPoster;

    if (targetPoster) {
      video.setAttribute('poster', targetPoster);
    } else {
      video.removeAttribute('poster');
    }

    video.dataset.currentPoster = targetPoster || '';

    return targetSrc;
  }

  function setVideoSource(video) {
    if (!video) {
      return;
    }

    const targetSrc = setVideoPoster(video);

    if (!targetSrc || video.dataset.currentSrc === targetSrc) {
      return;
    }

    video.pause();
    video.src = targetSrc;
    video.load();
    video.dataset.currentSrc = targetSrc;
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

      const updatePosters = function () {
        swiperElement.querySelectorAll('video').forEach(function (video) {
          setVideoPoster(video);
        });
      };

      const prepareActiveVideo = function () {
        pauseVideos(swiperElement);
        const activeVideo = swiperElement.querySelector('.swiper-slide-active video');
        setVideoSource(activeVideo);
        playVideo(activeVideo);
      };

      const swiper = new Swiper(swiperElement, {
        loop: true,
        autoplay: {
          delay: 5000,
          disableOnInteraction: false,
        },
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
            updatePosters();
            if (swiper.autoplay && typeof swiper.autoplay.stop === 'function') {
              swiper.autoplay.stop();
            }
          },
          slideChange: function () {
            prepareActiveVideo();
          },
        },
      });

      const refreshWithDebounce = debounce(function () {
        updatePosters();
        prepareActiveVideo();
      }, 200);

      mobileQuery.addEventListener('change', refreshWithDebounce);
      window.addEventListener('resize', refreshWithDebounce);

      window.addEventListener('load', function () {
        prepareActiveVideo();
        if (swiper.autoplay && typeof swiper.autoplay.start === 'function') {
          swiper.autoplay.start();
        }
      });
    });
  });
})();
