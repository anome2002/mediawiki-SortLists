<?php
class Sort2Hooks {

    /**
     * Setups the mediawiki hook
     *
     * @param Parser $parser
     * Author: Updated by Edgar Agayi patly based on the original sort2 extension that is depreciated
     */
    public static function onParserFirstCallInit( $parser ) {
        $parser->setHook( 'sort2', [ __CLASS__, 'onSort2Tag' ] );
    }

    /**
     * Callback that sorts the content within <sort2> tags
     *
     * Available options:
     *
     * * type string, to control the type of list to output, defaults to "ul"
     * * order string, to control the order of sorting, defaults to "ascending"
     * * case string, to control the case sensitivity of sorting, defaults to "insensitive"
     *
     * Sample Usage:
     *
     * <code>
     * <sort2 type="ol" order="descending" case="sensitive" />
     * </code>
     *
     * Gets the sorted list as an ordered list in descending order, sorted case sensitive.
     *
     * @param string|null $text
     * @param array $params Additional parameters passed as attributes to sort2 tag
     * @param Parser $parser The Wiki Parser Object
     * @return string
     */

	public static function onSort2Tag( $input, $args, $parser, $frame ) {
// Remove asterisks before wikilinks
    $input = preg_replace( '/\* ?(\[\[.+?\]\])/i', '$1', $input );
		// Convert input to list items
		$list_items = explode( "\n", $input );
		foreach ( $list_items as &$list_item ) {
			$list_item = "<li>$list_item</li>\n";
		}
		$input = implode( '', $list_items );

		// Sort the list items
		$input = self::sortList( $input, $args );

		// Wrap the sorted list items in an unordered list
		$output = "$input";

		// Parse the output wikitext and return it
		return $parser->recursiveTagParse( $output, $frame );
	}

	private static function sortList( $input, $args ) {
	// Remove asterisks before processing
	$input = preg_replace('/^\*\s*/', '', $input);

	// Split the input text into separate lines/paragraphs
	$lines = explode( "\n", $input );

	// Extract the link text and link target from each line (if applicable)
	$line_data = array();
	foreach ( $lines as $line ) {
		// Check for wikilinks
		$link_text = '';
		$link_target = '';
		$match_count = preg_match_all( '/\[\[(.+?)\]\]/', $line, $matches, PREG_SET_ORDER );
		if ( $match_count > 0 ) {
			foreach ( $matches as $match ) {
				$link_text .= $match[1];
				$link_target .= $match[1];
			}
		} else {
			$link_text = $line;
			$link_target = $line;
		}

		$line_data[] = array( 'line' => $line, 'link_text' => $link_text, 'link_target' => $link_target );
	}

	// Sort the lines based on the link text
	if ( isset( $args['case'] ) && $args['case'] === 'sensitive' ) {
		// Case-sensitive sorting
		usort( $line_data, function( $a, $b ) {
			return strnatcmp( $a['link_text'], $b['link_text'] );
		} );
	} else {
		// Case-insensitive sorting
		usort( $line_data, function( $a, $b ) {
			return strnatcasecmp( $a['link_text'], $b['link_text'] );
		} );
	}

	// Build the sorted list items
	$output = '';
	foreach ( $line_data as $line ) {
		$output .= $line['line'] . "\n";
	}

	return $output;
}
}
