<?php

    class inaccessible_callback_exception extends Exception {
    }


$plugin_basename;

    $wp_function_calls = array();

    $wp_function_calls["add_action"] = array();
    $wp_function_calls["add_filter"] = array();
    $wp_function_calls["register_activation_hook"] = array();
    $wp_function_calls["register_deactivation_hook"] = array();
    $wp_function_calls["register_setting"] = array();
    $wp_function_calls["get_option"] = array();
    $wp_function_calls["wp_enqueue_style"] = array();
    $wp_function_calls["wp_enqueue_script"] = array();
    $wp_function_calls["add_options_page"] = array();
    $wp_function_calls["__"] = array();
    $wp_function_calls["admin_url"] = array();
    $wp_function_calls["esc_html"] = array();
    $wp_function_calls["get_admin_page"] = array();

    $plugin_options = array();

    function add_call_to_register_function($function_name, $args) {
        global $wp_function_calls;
        if (!isset($wp_function_calls[$function_name]) ) {
            return;
        }
        array_push($wp_function_calls[$function_name], $args);
    }

    class expect_result {
        private $count;

        public function __construct($count) {
            $this->count = $count;
        }

        public function to_be_truthy() {
            return $this->count >0;
        }

        public function to_be_falsy() {
            return !$this->to_be_truthy();
        }

        public function count() {
            return ($this->count<0)? 0 : $this->count;
        }
    }

    class expect_runner {
        private $function_name;
        private $wp_function_calls;

        public function __construct($function_name, $wp_function_calls) {
            $this->function_name = $function_name;
            $this->wp_function_calls = $wp_function_calls;
        }

        public function to_have_been_called() {
            if (!isset($this->wp_function_calls[$this->function_name]) ) {
                return new expect_result(-1);
            }

            return new expect_result(sizeof($this->wp_function_calls[$this->function_name]));
        }

        public function to_have_been_called_with() {
            $args = func_get_args();
            if (!isset($this->wp_function_calls[$this->function_name]) ) {
                return new expect_result(-1);
            }

            $call_count = 0;

            foreach ($this->wp_function_calls[$this->function_name] as $call_info ) {
                if (serialize($args) == serialize($call_info) ) {
                    $call_count++;
                }
            }

            return new expect_result($call_count);
        }
    }

    function expect($function_name) {
        global $wp_function_calls;
        return new expect_runner($function_name, $wp_function_calls);
    }

    function plugin_dir_path() {
        return getcwd() . "/";
    }

    function plugin_basename() {
        global $plugin_basename;
        return $plugin_basename;
    }

    function set_plugin_basename($name) {
        global $plugin_basename;
        $plugin_basename = $name;
    }

    function set_option($options) {
        global $plugin_options;
        $plugin_options = $options;
    }

    function clear_mock_for($function_name) {
        global $wp_function_calls;
        if (!isset($wp_function_calls[$function_name]) ) return;

        $wp_function_calls[$function_name] = array();
    }

    function clear_options() {
        global $plugin_options;
        $plugin_options = array();
    }

    function clear_all_mocks() {
        global $wp_function_calls, $plugin_options;

        clear_options();

        foreach ($wp_function_calls as $key => $function_call) {
            clear_mock_for($key);
        }


    }

    function get_option() {
        global $plugin_options;
        add_call_to_register_function("get_option", func_get_args() );
        return $plugin_options;
    }

    function file_is_accessible($filename) {
        if (! file_exists($filename) )
            throw new inaccessible_callback_exception("Can't find file " . $filename );
    }

    function method_callback_is_accessible($obj, $method_name) {
        if (! method_exists($obj, $method_name) )
            throw new inaccessible_callback_exception("Can't find method " . $method_name . " in " . get_class($obj) );
    }

    function function_callback_is_accessible($filename, $function_name) {
        if (! file_exists($filename) )
            throw new inaccessible_callback_exception("can't find function " . $function_name . " in file " . $filename);
        include_once $filename;
        if (! function_exists($function_name) )
            throw new inaccessible_callback_exception("can't find function " . $function_name . " in file " . $filename);
    }

    function __($arg1, $arg2) {
        add_call_to_register_function("__", array($arg1, $arg2) );
        return $arg1 . $arg2;
    }

    function admin_url($input) {
        add_call_to_register_function("admin_url", array($input) );
        return $input;
    }

    function add_action() {
        add_call_to_register_function("add_action", func_get_args() );
        method_callback_is_accessible(func_get_args()[1][0], func_get_args()[1][1]);
    }

    function add_filter() {
        add_call_to_register_function("add_filter", func_get_args() );
        method_callback_is_accessible(func_get_args()[1][0], func_get_args()[1][1]);
    }

    function register_activation_hook() {
        add_call_to_register_function("register_activation_hook", func_get_args() );
        function_callback_is_accessible(func_get_args()[0], func_get_args()[1] );
    }

    function register_deactivation_hook() {
        add_call_to_register_function("register_deactivation_hook", func_get_args() );
        function_callback_is_accessible(func_get_args()[0], func_get_args()[1] );
    }

    function register_setting() {
        add_call_to_register_function("register_setting", func_get_args() );
        method_callback_is_accessible(func_get_args()[2][0], func_get_args()[2][1]);
    }

    function wp_enqueue_style() {
        file_is_accessible(func_get_args()[1]);
        add_call_to_register_function("wp_enqueue_style", func_get_args() );
    }

    function wp_enqueue_script() {
        file_is_accessible(func_get_args()[1]);
        add_call_to_register_function("wp_enqueue_script", func_get_args() );
    }

    function add_options_page() {
        $args = func_get_args();
        method_callback_is_accessible($args[4][0], $args[4][1]);
        add_call_to_register_function("add_options_page", $args );
    }

    function get_admin_page() {
        add_call_to_register_function("get_admin_page", func_get_args() );
        return "";
    }

    function esc_html($input) {
        add_call_to_register_function("esc_html", array($input) );
        return $input;
    }
?>
