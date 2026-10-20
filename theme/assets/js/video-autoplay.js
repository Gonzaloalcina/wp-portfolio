/**
 * Autoplay for short, silent screen recordings ([data-autoplay] videos in
 * the content): each one plays while it is on screen and pauses when it
 * leaves. With "reduce motion" on, nothing plays by itself: the poster and
 * the controls stay. The videos keep their controls, so anyone can pause.
 */

const reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' );
const videos = document.querySelectorAll( 'video[data-autoplay]' );

const observer = new IntersectionObserver(
	( entries ) => {
		for ( const { target, isIntersecting } of entries ) {
			if ( isIntersecting && ! reduce.matches ) {
				target.play().catch( () => {} );
			} else {
				target.pause();
			}
		}
	},
	{ threshold: 0.5 }
);

for ( const video of videos ) {
	video.muted = true;
	observer.observe( video );
}

reduce.addEventListener( 'change', () => {
	if ( reduce.matches ) {
		videos.forEach( ( video ) => video.pause() );
	}
} );
