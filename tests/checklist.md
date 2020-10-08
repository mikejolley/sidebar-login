# Testing Checklist

## Manual testing checklist:

- [x] Plugin activates without notices
- [x] Widget available in widget settings
- [x] All widget settings have correct defaults on first insertion
- [x] Widget is functional before making changes to widget settings
- [x] All widget settings update and persist
- [x] All widget settings influence the frontend displays
- [x] Log in functionality works on the frontend
  - [x] Validation occurs on missing fields
  - [x] Errors show when using incorrect login details
  - [x] Form is functional when JS is disabled
  - [x] Login works and redirects correctly after using correct details
- [x] After logging in, correct details are shown
  - [x] Log out link functions
  - [x] Avatar is displayed
  - [x] Placeholders all replace text correctly
  - [x] Custom links are shown
- [x] Multiple widgets on same page work independently
- [x] Upgrading from a legacy 2.7.x widget to the new 3.x version remains functional
- [x] Compatibility
  - [x] Runs on WP 5.0
  - [x] Runs on PHP 5.6
  - [x] the_widget support
  - [x] legacy the_widget support
  - [x] Appearance acceptable across default themes:
    - [x] Twenty Eleven
    - [x] Twenty Twelve
    - [x] Twenty Thirteen
    - [x] Twenty Fourteen
    - [x] Twenty Fifteen
    - [x] Twenty Sixteen
    - [x] Twenty Seventeen
    - [x] Twenty Eighteen
    - [x] Twenty Nineteen
    - [x] Twenty Twenty

## Post deployment checklist

- [ ] Stable tag is up to date on wordpress.org
- [ ] Tag exists on wordpress.org
- [ ] Plugin contains the /build/ directory
- [ ] Plugin contains the /vendor/ directory and autoloader
