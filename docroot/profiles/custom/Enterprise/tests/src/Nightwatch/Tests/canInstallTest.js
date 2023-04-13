module.exports = {
  '@tags': ['enterprise'],
  before: function(browser) {
  },
  after: function(browser) {
  },
  'Run the install profile': (browser) => {
    browser
      .drupalRelativeURL('core/install.php')
      .waitForElementVisible('body', 1000)
      .assert.visible('.install-page')      
      .waitForElementVisible('.page-wrapper', 1000)
      .assert.visible('.site-name')
      .assert.containsText('.site-name', 'Enterprise')
      .click('#edit-submit')
      .waitForElementVisible('body', 1000)
      .assert.containsText('.heading-c', 'Installing Enterprise')
      .waitForElementVisible('.install-configure-form', 600000)
      .setValue('input#edit-site-name', "Test Site")
      .setValue('input#edit-site-mail', "noreply@vmlyr.com")
      .setValue('input#edit-account-name', "admin")
      .setValue('input#edit-account-pass-pass1', "CorrectHorseBatteryStaple")
      .setValue('input#edit-account-pass-pass1', "CorrectHorseBatteryStaple")
      .setValue('input#edit-account-mail', "noreply@vmlyr.com")
      .click('#edit-submit')
      .waitForElementVisible('.layout-container', 50000)
      .end();
  },

};