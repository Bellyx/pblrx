function slideCountry(direction) {
  console.log('CLICK', direction);

  const slider = document.getElementById('countrySlider');
  console.log('SLIDER:', slider);

  if (!slider) {
    console.error('❌ ไม่เจอ #countrySlider');
    return;
  }

  const card = slider.querySelector('.country-card');
  console.log('CARD:', card);

  if (!card) {
    console.error('❌ ไม่เจอ .country-card');
    return;
  }

  const cardWidth = card.offsetWidth + 40;
  slider.scrollLeft += direction * cardWidth * 5;
}