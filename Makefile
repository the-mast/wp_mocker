test:
	phpunit
	
clean:
	rm -rf output

package: clean test
	mkdir output output/wp_mocker
	cp wp_mocker.php output/wp_mocker
	cd output; \
		zip -r wp_mocker.zip wp_mocker
	rm -rf output/wp_mocker
