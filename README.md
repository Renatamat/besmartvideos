# BeSmart Video Slider

## Sekcje video / placement

Moduł obsługuje teraz 2 niezależne placementy:

- `small_sequence` – dotychczasowa sekcja renderowana w hooku `displayHome`.
- `large_sequence` – nowa większa sekcja renderowana w customowym hooku `displayBesmartVideosLarge` lub w `displayTopColumn`.

## Back Office

W BO są dostępne dwie osobne zakładki modułu:

- **Videos (Small)** (`AdminBesmartVideoSlider`)
- **Videos (Large)** (`AdminBesmartVideoSliderLarge`)

Każda zakładka zarządza własną listą video (kolejność, aktywność, edycja, usuwanie).

Dla każdego slajdu można ustawić dodatkowo **Opis (HTML)** oraz przycisk (etykieta + URL), które wyświetlają się na dole slajdu.

## Integracja Front Office

- Mała sekcja: pozostaje bez zmian (`hookDisplayHome`, tpl: `views/templates/hook/slider.tpl`).
- Duża sekcja: custom hook `displayBesmartVideosLarge` (`hookDisplayBesmartVideosLarge`) lub `displayTopColumn` (`hookDisplayTopColumn`), tpl: `views/templates/hook/large.tpl`.

Aby wstawić duże wideo w wybranym miejscu motywu, dodaj w odpowiednim pliku Smarty:

```smarty
{hook h='displayBesmartVideosLarge'}
{* alternatywnie: *}
{hook h='displayTopColumn'}
```

## Upgrade

Aktualizacja do wersji `1.1.0`:

- dodaje kolumnę `placement` do tabeli slajdów,
- ustawia istniejące rekordy jako `small_sequence`,
- rejestruje hooki `displayBesmartVideosLarge` oraz `displayTopColumn`,
- dodaje nową zakładkę BO dla dużych video.


Aktualizacja do wersji `1.1.1`:

- dodaje kolumnę `description` (TEXT) do tabeli `besmartvideoslider_slides_lang`,
- umożliwia wyświetlanie opisu HTML pod video oraz przycisku pod opisem.


Aktualizacja do wersji `1.1.2`:

- dodaje kolumnę `button_label_category` do tabeli `besmartvideoslider_slides_lang`,
- pozwala ustawić osobny napis przycisku dla hooka `displayBesmartVideosLarge` (np. na stronie kategorii),
- przy pustym polu używany jest domyślny `button_label`.
