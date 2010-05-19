<?php

	class DbLogListener {
		
		public function error() {
			pr(func_get_args());
		}
		
	}