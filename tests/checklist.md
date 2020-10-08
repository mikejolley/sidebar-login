# Testing Checklist

## Manual testing checklist:

- [ ] Plugin activates without notices
- [ ] Widget available in widget settings
- [ ] All widget settings have correct defaults on first insertion
- [ ] Widget is functional before making changes to widget settings
- [ ] All widget settings update and persist
- [ ] All widget settings influence the frontend displays
- [ ] Log in functionality works on the frontend
  - [ ] Validation occurs on missing fields
  - [ ] Errors show when using incorrect login details
  - [ ] Form is functional when JS is disabled
  - [ ] Login works and redirects correctly after using correct details
- [ ] After logging in, correct details are shown
  - [ ] Log out link functions
  - [ ] Avatar is displayed
  - [ ] Placeholders all replace text correctly
  - [ ] Custom links are shown
- [ ] Multiple widgets on same page work independently
- [ ] Upgrading from a legacy 2.7.x widget to the new 3.x version remains functional
- [ ] Compatibility
  - [ ] Runs on WP 5.2
  - [ ] Runs on PHP 5.6
  - [ ] Appearance acceptable across default themes:
    - [ ] Twentysixteen
    - [ ] Twentyseventeen
    - [ ] Twentyeighteen
    - [ ] Twentynineteen
    - [ ] Twentytwenty

## Post deployment checklist

- [ ] Stable tag is up to date on wordpress.org
- [ ] Tag exists on wordpress.org
- [ ] Plugin contains the /build/ directory
- [ ] Plugin contains the /vendor/ directory and autoloader
