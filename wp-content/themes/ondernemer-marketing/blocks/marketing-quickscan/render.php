<?php
/**
 * Marketing Quickscan , SSR.
 *
 * @package OndernemerMarketing
 */
defined( 'ABSPATH' ) || exit;

$questions = [
	[
		'q'       => 'Heb je een geschreven marketingstrategie?',
		'options' => [ [ 'Nee', 0 ], [ 'Gedeeltelijk', 1 ], [ 'Ja, volledig', 2 ] ],
	],
	[
		'q'       => 'Hoe vaak meet je je marketingresultaten?',
		'options' => [ [ 'Nooit', 0 ], [ 'Per kwartaal', 1 ], [ 'Wekelijks of dagelijks', 2 ] ],
	],
	[
		'q'       => 'Heb je een lead-magnet die converteert?',
		'options' => [ [ 'Nee', 0 ], [ 'Een eenvoudig PDF', 1 ], [ 'Ja, met meetbare opt-ins', 2 ] ],
	],
	[
		'q'       => 'Verstuur je een gestructureerde e-mailnurturing reeks?',
		'options' => [ [ 'Nee', 0 ], [ 'Ad-hoc', 1 ], [ 'Ja, automatisch', 2 ] ],
	],
	[
		'q'       => 'Run je betaalde campagnes (Google / Meta)?',
		'options' => [ [ 'Nee', 0 ], [ 'Ja, zonder duidelijke ROI', 1 ], [ 'Ja, met meetbare ROI', 2 ] ],
	],
	[
		'q'       => 'Heb je een dashboard met je belangrijkste KPI\'s?',
		'options' => [ [ 'Nee', 0 ], [ 'Excel', 1 ], [ 'Realtime dashboard', 2 ] ],
	],
];
$id = wp_unique_id( 'ondm-quickscan-' );
?>
<section class="ondm-tool ondm-quickscan" id="<?php echo esc_attr( $id ); ?>" <?php echo get_block_wrapper_attributes(); ?>>
	<div class="ondm-tool__inner">
		<h2 class="ondm-tool__title">Marketing Quickscan</h2>
		<p class="ondm-tool__lead">6 vragen over je huidige marketing. Direct een score + advies.</p>

		<form class="ondm-quickscan__form" data-ondm-form>
			<?php foreach ( $questions as $i => $q ) : ?>
				<fieldset class="ondm-quickscan__question">
					<legend class="ondm-quickscan__legend"><?php echo (int) ( $i + 1 ); ?>. <?php echo esc_html( $q['q'] ); ?></legend>
					<?php foreach ( $q['options'] as $j => $opt ) : ?>
						<label class="ondm-quickscan__option">
							<input type="radio" name="q<?php echo (int) $i; ?>" value="<?php echo (int) $opt[1]; ?>"<?php echo 0 === $j ? ' required' : ''; ?>>
							<span><?php echo esc_html( $opt[0] ); ?></span>
						</label>
					<?php endforeach; ?>
				</fieldset>
			<?php endforeach; ?>
			<button type="submit" class="wp-element-button ondm-quickscan__submit">Bereken mijn score</button>
		</form>

		<div class="ondm-quickscan__result" data-ondm-result hidden aria-live="polite">
			<h3 class="ondm-quickscan__result-title">Jouw score: <span data-ondm-out="score">0</span> / 12</h3>
			<p class="ondm-quickscan__result-band" data-ondm-out="band"></p>
			<p class="ondm-quickscan__result-advice" data-ondm-out="advice"></p>
			<div class="ondm-tool__cta">
				<a class="wp-block-button__link wp-element-button ondm-tool__cta-btn" href="/contact/">Plan gratis intake</a>
			</div>
		</div>
	</div>
</section>
