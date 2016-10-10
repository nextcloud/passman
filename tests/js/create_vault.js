var protractor = require('protractor');


function waitForElement ( _element ) {
	var EC = protractor.ExpectedConditions;
	return browser.wait(EC.presenceOf(_element));
};

describe('Passman', function () {
	var _createdVaultName;

	it('Should login', function () {
		browser.ignoreSynchronization = true;
		browser.get('http://localhost:8080');

		element(by.name('user')).sendKeys('example');
		element(by.name('password')).sendKeys('example');
		element(by.id('submit')).click();

		var el = element(by.id('expandDisplayName'));
		browser.driver.wait(waitForElement(el));
		el.getText().then(function (text) {
			expect(text).toEqual('example');
		})
	});

	it('Should navigate to app', function () {
		browser.get('http://localhost:8080/index.php/apps/passman/#/');
		var el = element(by.xpath("//div[@id='app-content-wrapper']//li[.='+ Create a new vault']"));
		browser.driver.wait(waitForElement(el));
		element(by.xpath("//div[@id='app-content-wrapper']//li[.='+ Create a new vault']")).getText().then(function (text) {
			expect(text).toEqual('+ Create a new vault');
		})
	});

	it('Should create a vault', function () {
		element(by.xpath("//div[@id='app-content-wrapper']//li[.='+ Create a new vault']")).click().then(function () {
			_createdVaultName = Math.random().toString(36).substr(2, 5);
			element(by.xpath("//div[@class='login_form']/div[1]/input")).sendKeys(_createdVaultName);
			element(by.xpath("//div[@class='login_form']/div[2]/input")).clear();
			element(by.xpath("//div[@class='login_form']/div[2]/input")).sendKeys("example");
			element(by.xpath("//div[@class='login_form']/div[3]/input")).clear();
			element(by.xpath("//div[@class='login_form']/div[3]/input")).sendKeys("example");
			element(by.xpath("//div[@class='button_wrapper']//div[.='Create vault']")).click().then(function () {
				browser.driver.wait(waitForElement(element(by.id('passman-controls'))));
				element(by.xpath("//div[@id='passman-controls']/div[1]/div[1]")).getText().then(function (text) {
					expect(text).toEqual(_createdVaultName);
				});
			});

		})
	});
});