<?php

/**
 * Hooks for Interactive block message
 */
class InteractiveBlockMessageHooks {
	/**
	 * @param array &$vars
	 * @return bool
	 */
	public static function magicWordSet( &$vars ) {
		$vars[] = 'USERBLOCKED';
		return true;
	}

	/**
	 * Function check if user is blocked, return true
	 * user blocked status is passed to $ret
	 * @param Parser $parser
	 * @param array &$varCache
	 * @param string $index
	 * @param array &$ret
	 * @return bool
	 */
	public static function parserGetVariable( $parser, &$varCache, $index, &$ret ) {
		if ( $index != 'USERBLOCKED' ) {
			return true;
		}

		$title = $parser->getTitle();
		if ( $title->getNamespace() != NS_USER && $title->getNamespace() != NS_USER_TALK ) {
			$ret = $varCache[$index] = 'unknown';
			return true;
		}

		$user = User::newFromName( $title->getBaseText() );
		if ( $user instanceof User ) {
			if ( !$user->getBlock() ) {
				$ret = $varCache[$index] = 'false';
				return true;
			}
			// if user is blocked it's pretty much possible they will be unblocked one day :)
			// so we enable cache for shorter time only so that we can recheck later
			// if they weren't already unblocked - if there is a better way to do that, fix me
			$expiry = $user->getBlock()->getExpiry();
			if ( is_numeric( $expiry ) ) {
				// sometimes this is 'infinityinfinity'. in that case, use the default cache expiry time.
				$expiry = wfTimestamp( TS_UNIX, $expiry ) - wfTimestamp( TS_UNIX );
				if ( $expiry > 0 ) {
					// just to make sure
					$parser->getOutput()->updateCacheExpiry( $expiry );
				}
			}
			$ret = $varCache[$index] = 'true';
			return true;
		}
		$ret = $varCache[$index] = 'unknown';
		return true;
	}
}
