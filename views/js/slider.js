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

    // na wszelki wypadek wyłącz pętlę, żeby 'ended' w ogóle się wywołał
    video.removeAttribute('loop');

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
      // wyczyść poprzednie nasłuchiwanie końca filmu
      video.onended = null;
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

      let swiper; // potrzebne, żeby użyć w listenerze 'ended'

      const updatePosters = function () {
        swiperElement.querySelectorAll('video').forEach(function (video) {
          setVideoPoster(video);
        });
      };

      const prepareActiveVideo = function () {
        // zatrzymaj wszystkie filmy i usuń nasłuchiwacze
        pauseVideos(swiperElement);

        // znajdź video w aktualnie aktywnym slajdzie
        const activeVideo = swiperElement.querySelector('.swiper-slide-active video');
        if (!activeVideo) {
          return;
        }

        // ustaw źródło
        setVideoSource(activeVideo);

        // ustaw reakcję na koniec filmu – PRZEŁĄCZ SLIDE
        activeVideo.onended = function () {
          if (swiper && !swiper.destroyed) {
            swiper.slideNext();
          }
        };

        // odtwórz
        playVideo(activeVideo);
      };

      swiper = new Swiper(swiperElement, {
        loop: true,
        // USUWAMY autoplay oparty na czasie
        // autoplay: { ... }

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
            prepareActiveVideo();
          },
          // po zakończeniu animacji zmiany slajdu przygotuj nowe video
          slideChangeTransitionEnd: function () {
            prepareActiveVideo();
          }
        },
      });

      const refreshWithDebounce = debounce(function () {
        updatePosters();
        prepareActiveVideo();
      }, 100);

      mobileQuery.addEventListener('change', refreshWithDebounce);
      window.addEventListener('resize', refreshWithDebounce);
    });
  });
})();
