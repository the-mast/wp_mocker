<?php
use PHPUnit\Framework\TestCase;

class test_expect_result extends TestCase {
    function test_shouldHaveToBeTruthyTrueAndToBeFalsyFalse() {
        $result = new expect_result(1);
        $this->assertTrue($result->to_be_truthy() );
        $this->assertFalse($result->to_be_falsy() );
    }

    function test_countShouldBeOne() {
        $result = new expect_result(1);
        $this->assertEquals(1, $result->count() );
    }

    function test_countShouldBezero() {
        $result = new expect_result(0);
        $this->assertEquals(0, $result->count() );
    }

    function test_countShouldBezeroAsNegativeCountMakesNoSense() {
        $result = new expect_result(-1);
        $this->assertEquals(0, $result->count() );
    }

    function test_truthyShouldBeFalseAndFalsyTrueAsInstantiatingWithAValueLessThanOne() {
        $result = new expect_result(-1);
        $this->assertTrue(!$result->to_be_truthy() );
        $this->assertTrue($result->to_be_falsy() );
    }
}

class test_expect_runner extends TestCase {
    private $wp_function_calls = array();

    function setUp() {
        $this->wp_function_calls["test"] = array();
        array_push($this->wp_function_calls["test"], array(1) );
    }

    function test_theResultOf_to_have_been_calledShouldBeFalsyForAnUnknownFunction() {
        $runner = new expect_runner("unknown", $this->wp_function_calls);
        $this->assertTrue($runner->to_have_been_called()->to_be_falsy() );
    }

    function test_theResultOf_to_have_been_calledShouldBeFalsyAsNoCallHasBeenMade() {
        $this->wp_function_calls["test"] = array();
        $runner = new expect_runner("test", $this->wp_function_calls);
        $this->assertTrue($runner->to_have_been_called()->to_be_falsy() );
    }

    function test_theResultOf_to_have_been_calledShouldBeTruthy() {
        $runner = new expect_runner("test", $this->wp_function_calls);
        $this->assertTrue($runner->to_have_been_called()->to_be_truthy() );
    }

    function test_theResultOf_to_have_been_called_withShouldBeFalsyForAnUnknownFunction() {
        $runner = new expect_runner("unknown", $this->wp_function_calls);
        $this->assertTrue($runner->to_have_been_called_with(1)->to_be_falsy() );
    }

    function test_theResultOf_to_have_been_called_withShouldBeFalsyAsNoCallHasBeenMade() {
        $this->wp_function_calls["test"] = array();
        $runner = new expect_runner("test", $this->wp_function_calls);
        $this->assertTrue($runner->to_have_been_called_with(1)->to_be_falsy() );
    }

    function test_theResultOf_to_have_been_called_withShouldBeTruthy() {
        $runner = new expect_runner("test", $this->wp_function_calls);
        $this->assertTrue($runner->to_have_been_called_with(1)->to_be_truthy() );
    }
}

//This function is used in some register_hook calls and is merely there to help existence checks
function dummy_function() {
}

//dummy_class is merely created for existence test
class dummy_class {
    public function __construct() {
    }

    public function dummy_action() {
    }

    public function dummy_filter() {
    }
}
class test_test_helper extends TestCase {

    function setUp() {
        clear_all_mocks();
    }

    function test_plugin_dir_pathShouldBeSimilarTo_getcwd() {
        $this->assertEquals(getcwd() . "/", plugin_dir_path() );
    }

    function test_optionsShouldBeEmpty() {
        $this->assertTrue(empty(get_option() ) );
    }

    function test_optionAriEqualsOne () {
        $options = array();
        $options["ari"] = "One";
        set_option($options );

        $expected = array();
        $expected["ari"] = "One";
        $this->assertEquals(serialize($expected), serialize(get_option() ) );
    }

    function test_shouldClearOptions() {
        $options = array();
        $options["ari"] = "One";
        set_option($options );

        $this->assertTrue(!empty(get_option() ) );
        clear_options();
        $this->assertTrue(empty(get_option() ) );
    }

    private function populate_wp_function_calls() {
        global $wp_function_calls;
        foreach ($wp_function_calls as $key => $call_info ) {
            array_push($wp_function_calls[$key] , $key);
        }
    }

    function test_shouldClearTheMockForAddAction() {
        global $wp_function_calls;
        $this->populate_wp_function_calls();
        $this->assertTrue(!empty($wp_function_calls["add_action"]) );
        clear_mock_for("add_action");
        foreach ($wp_function_calls as $key => $call_info ) {
            if ($key == "add_action" )
                $this->assertTrue(empty($wp_function_calls[$key]) );
            else
                $this->assertTrue(!empty($wp_function_calls[$key]) );
        }
    }

    function test_ThereShouldBeNoChangeTo_wp_function_calls() {
        global $wp_function_calls;
        $before = serialize($wp_function_calls);
        clear_mock_for("junk");
        $after = serialize($wp_function_calls);
        $this->assertEquals($before, $after);
    }

    function test_ItShouldClear_wp_function_callsAsWellAs_options() {
        global $wp_function_calls;
        $this->populate_wp_function_calls();
        set_option(array("one", "two") );

        $this->assertTrue(!empty(get_option() ) );
        foreach ($wp_function_calls as $key => $call_info ) 
            $this->assertTrue(!empty($wp_function_calls[$key]) );
        clear_all_mocks();

        foreach ($wp_function_calls as $key => $call_info )
            $this->assertTrue(empty($wp_function_calls[$key]) );
        $this->assertTrue(empty(get_option() ) );
    }

    function test_itShouldReturnAnInstanceOf_expect_runner() {
        $this->assertTrue(expect("some_function") instanceof expect_runner);
    }

    function test_add_actionShouldthrowAnException() {
        try {
            add_action("abc", array($this, "junk") );
            $this->assertTrue(false);
        } catch (inaccessible_callback_exception $e) {
            $this->assertTrue(true);
        }
    }

    function test_add_actionShouldBeTruthy() {
        $this->assertTrue(expect("add_action")->to_have_been_called()->to_be_falsy() );
        $dummy = new dummy_class();
        add_action('admin_init', array($dummy, 'dummy_action') );
        $this->assertTrue(expect("add_action")->to_have_been_called()->to_be_truthy() );
    }

    function test_add_filterShouldthrowAnException() {
        try {
            add_filter("abc", array($this, "junk") );
            $this->assertTrue(false);
        } catch (inaccessible_callback_exception $e) {
            $this->assertTrue(true);
        }
    }

    function test_add_filterShouldBeTruthy() {
        $this->assertTrue(expect("add_filter")->to_have_been_called()->to_be_falsy() );
        $dummy = new dummy_class();
        add_filter('admin_init', array($dummy, 'dummy_action') );
        $this->assertTrue(expect("add_filter")->to_have_been_called()->to_be_truthy() );
    }

    function test_register_activation_hookShouldthrowAnException() {
        try {
            register_activation_hook("abc", "junk");
            $this->assertTrue(false);
        } catch (inaccessible_callback_exception $e) {
            $this->assertTrue(true);
        }
    }

    function test_register_activation_hookShouldBeTruthy() {
        $this->assertTrue(expect("register_activation_hook")->to_have_been_called()->to_be_falsy() );
        register_activation_hook(__FILE__, "dummy_function");
        $this->assertTrue(expect("register_activation_hook")->to_have_been_called()->to_be_truthy() );
    }

    function test_register_deactivation_hookShouldthrowAnException() {
        try {
            register_deactivation_hook("abc", "junk");
            $this->assertTrue(false);
        } catch (inaccessible_callback_exception $e) {
            $this->assertTrue(true);
        }
    }

    function test_register_deactivation_hookShouldBeTruthy() {
        $this->assertTrue(expect("register_activation_hook")->to_have_been_called()->to_be_falsy() );
        register_deactivation_hook(__FILE__, "dummy_function");
        $this->assertTrue(expect("register_deactivation_hook")->to_have_been_called()->to_be_truthy() );
    }

    function test_register_settingShouldthrowAnException() {
        try {
            register_setting("abc", $this, array($this, "junk") );
            $this->assertTrue(false);
        } catch (inaccessible_callback_exception $e) {
            $this->assertTrue(true);
        }
    }

    function test_register_settingShouldBeTruthy() {
        $this->assertTrue(expect("register_setting")->to_have_been_called()->to_be_falsy() );
        $dummy = new dummy_class();
        register_setting("dummy", "dummy", array($dummy, 'dummy_action') );
        $this->assertTrue(expect("register_setting")->to_have_been_called()->to_be_truthy() );
    }

    function test_wp_enqueue_styleShouldThrowAnException() {
        try {
            wp_enqueue_style("junk", "junk", array(), "1.0.0", "all");
            $this->assertTrue(false);
        } catch (inaccessible_callback_exception $e) {
            $this->assertTrue(true);
        }
    }

    function test_wp_enqueue_styleShouldExpectToBeCalled() {
        wp_enqueue_style("junk", __file__, array(), "1.0.0", "all");
        $this->assertTrue(expect("wp_enqueue_style")->to_have_been_called()->to_be_truthy() );
    }

    function test_wp_enqueue_scripShouldThrowAnException() {
        try {
            wp_enqueue_script("junk", "junk", array(), "1.0.0", "all");
            $this->assertTrue(false);
        } catch (inaccessible_callback_exception $e) {
            $this->assertTrue(true);
        }
    }

    function test_wp_enqueue_scriptShouldExpectToBeCalled() {
        wp_enqueue_script("junk", __file__, array(), "1.0.0", "all");
        $this->assertTrue(expect("wp_enqueue_script")->to_have_been_called()->to_be_truthy() );
    }

    function test_add_options_pageShouldThrowAnException() {
        $dummy = new dummy_class();
        try {
            add_options_page("junk", "junk", "1.0.0", "all", array($dummy, "junk") );
            $this->assertTrue(false);
        } catch (inaccessible_callback_exception $e) {
            $this->assertTrue(true);
        }
    }

    function test_add_options_page_ShouldExpectToBeCalled() {
        add_options_page("junk", "junk", "1.0.0", "all", array(new dummy_class(), "dummy_action") );
        $this->assertTrue(expect("add_options_page")->to_have_been_called()->to_be_truthy() );
    }

    function test_ShouldReturnInputAndRegisterAsACall() {
        $this->assertEquals("hello", __("hello"));
        $this->assertTrue(expect("__")->to_have_been_called()->to_be_truthy() );
    }

    function test_ShouldReturnInputAndRegisterACallTo_admin_url() {
        $this->assertEquals("hello", admin_url("hello"));
        $this->assertTrue(expect("admin_url")->to_have_been_called()->to_be_truthy() );
    }
}
