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
	public static function onSort2Tag( $text, array $params, Parser $parser ) {
		// Get parameters
		$type = isset( $params['type'] ) ? strtolower( $params['type'] ) : 'ul';
		$order = isset( $params['order'] ) ? strtolower( $params['order'] ) : 'ascending';
		$case = isset( $params['case'] ) ? strtolower( $params['case'] ) : 'insensitive';
		
		// Get content within <sort2> tags
		$content = '';
		if (preg_match('/^(?:<ol>|<ul>)(.*)(?:<\/ol>|<\/ul>)$/si', $text, $matches)) {
			// Check if the list is already formatted with asterisks
			if (preg_match('/^\*\s.*$/m', $matches[1])) {
				$content = $matches[1];
			} else {
				$list = array_map('trim', explode("\n", $matches[1]));
				$content = '';
				foreach ($list as $item) {
					$content .= "* $item\n";
				}
			}
		} else {
			$content = $text;
		}
		
		// Sort content
		$list = array_map('trim', explode("\n", $content));
		if ( $case == 'sensitive' ) {
			if ( $order == 'ascending' ) {
				sort( $list );
			} else {
				rsort( $list );
			}
		} else {
			if ( $order == 'ascending' ) {
				natcasesort( $list );
			} else {
				natsort( $list );
				$list = array_reverse( $list );
			}
		}

return $output;
// Build output
$output = '';
if ( $type == 'ul' ) {
    $output .= "<ul>\n";
    foreach ( $list as $item ) {
        // Check if the list item is already formatted as a list item and skip adding asterisks
        if (preg_match('/^\*+/', $item)) {
            $output .= "$item\n";
        } else {
            $output .= "<li>$item</li>\n";
        }
    }
    $output .= "</ul>\n";
} elseif ( $type == 'ol' ) {
    $output .= "<ol>\n";
    foreach ( $list as $item ) {
        // Check if the list item is already formatted as a list item and skip adding numbers
        if (preg_match('/^\d+\./', $item)) {
            $output .= "$item\n";
        } else {
            $output .= "<li>$item</li>\n";
        }
    }
    $output .= "</ol>\n";
}

return $output;
}
}
