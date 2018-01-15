#CHANGELOG#

Ver 2.0.18 [2018-01-15]:
-------------------------------
 - Minor Code Updates
 - Added placement status plot to Client Dashboard
 - Merge branch 'master' of https://github.com/tropotek/tk-table
 - Finished adding Skills item editors and managers, and migration


Ver 2.0.17 [2017-10-03]:
-------------------------------
 - Minor Code Updates


Ver 2.0.16 [2017-09-27]:
-------------------------------
 - Minor Code Updates
 - Merge branch 'master' of https://github.com/tropotek/tk-table
 - Finished base company view page.
 - Added Calendar and manager to company view
 - Fixed student company search table renderer
 - 252s-dev.vet.unimelb.edu.au
 - Finished Dynamic Form Fields
 - Added Company Categories
 - Updated Migration script
 - Final implementation of dynamic field system


Ver 2.0.15 [2017-06-15]:
-------------------------------
 - Minor Code Updates
 - 252s-dev.vet.unimelb.edu.au


Ver 2.0.14 [2017-05-26]:
-------------------------------
 - Minor Code Updates
 - 252s-dev.vet.unimelb.edu.au
 - Fixed crumbs, company table, Ui Tables, added logo2mapMarker converter


Ver 2.0.13 [2017-04-27]:
-------------------------------
 - Minor Code Updates
 - 252s-dev.vet.unimelb.edu.au


Ver 2.0.12 [2017-04-02]:
-------------------------------
 - Minor Code Updates
 - 252s-dev.vet.unimelb.edu.au


Ver 2.0.11 [2017-03-08]:
-------------------------------
 - Minor Code Updates
 - 252s-dev.vet.unimelb.edu.au


Ver 2.0.10 [2017-03-06]:
-------------------------------
 - Minor Code Updates
 - 252s-dev.vet.unimelb.edu.au


Ver 2.0.9 [2017-02-23]:
-------------------------------
 - Minor Code Updates


Ver 2.0.8 [2017-02-22]:
-------------------------------
 - Fixed up the code with new lib updates
 - Minor Code Updates
 - 252s-dev.vet.unimelb.edu.au


Ver 2.0.7 [2017-01-20]:
-------------------------------
 - Minor Code Updates
 - 252s-dev.vet.unimelb.edu.au
 - Added the column select javascript
 - Finalising Table column select action


Ver 2.0.6 [2016-12-30]:
-------------------------------
 - Upgraded to use new lib cass names
 - Fixed Orphaned sort bug
 - Minor Code Updates
 - 252s-dev.vet.unimelb.edu.au


Ver 2.0.5 [2016-11-11]:
-------------------------------
 - Minor Code Updates
 - 252s-dev.vet.unimelb.edu.au
 - Added OrderBy sortable javascript
 - Added orderBy table cell...
 - Started new comment rating sections
 - Started updating the Youtube queue
 - Added remote file handlers
 - Finished the Project Listing
 - Started added UI controllers for LTI pages


Ver 2.0.4 [2016-09-05]:
-------------------------------
 - Fixed the event list manager
 - Fixed table array handling
 - Added Html Cell rendderer for the Table lib
 - Adde new event dispatcher event parsing so we can see what events are available throughout the
   system getAvailableEvents()
 - Added Multi Selct to select field and added javascript plugin for dual select.
 - Added course enrollment system
 - Modified user management in administration
 - Added new tk mail lib
 - Implemented lib into project
 - Fixed table filter form issues
 - Checkbox broken still fixing it!
 - Updated Form File field and javascript plugin
 - Updated Edit Institution page
 - Change php version check to gt php5.0.0


Ver 2.0.3 [2016-07-10]:
-------------------------------
 - Added History, Search pages
 - Added Link management
 - Added Lock management
 - Finished user login, register
 - Started Wiki pages and routing
 - Updated RendererInterface and seperated teh show() to DisplayInterface
 - Finalised base template site
 - Fixed calles to new DB objects
 - Fixed Url to Uri class names and methods
 - Started to implement PSR7 interfaces, this will break most things using the URL
 - Updated code, added an update.md with info for the updated codes...


Ver 2.0.2 [2016-04-14]:
-------------------------------
 - Merge branch 'master' of git://github.com/tropotek/tk-table
 - General code format
 - Fixed Limit exception and fails quietly now.


Ver 2.0.1 [2016-03-23]:
-------------------------------
 - Finally added tabs and fieldsets to Form renderer
 - Fixed LTI classes and logic
 - Fixed date timezone issues with timestamp use \Tk\Date::parse({timestamp})


Ver 2.0.0 [2016-03-08]:
-------------------------------
 - Adde Lti Authentication, still need finishing....
 - Fixed Table Cell\Date
 - LDAP and authentication tidy up
 - Updated form calls to new form
 - Creating a simpler form object.
 - Finished basic user system.
 - Updated form, relized it needs to be refactored, see readme
 - Added postgress compatable queries to the \Tk\PDo object
 - Fixed some DB queries
 - Tiding up
 - Seperating symfony from tk-framework adding to App
 - Fixing DomTemplate documentation
 - Added new V2 files to the table lib master branch


Ver 1.2.8 [2015-04-13]:
-------------------------------
 - Merge branch 'master' of git://github.com/tropotek/tk-table
 - Updated top menu and added filter to project lists


Ver 1.2.7 [2015-03-31]:
-------------------------------
 - Fixed table rendering issue with limit causing long page loads


Ver 1.2.6 [2015-03-24]:
-------------------------------
 - Fixed AdditionalInfo field in placement editor Added additionalInfo field to placementManager Added
   title field to term for custom naming Added Goals Button to Edit placement Fixed Goals edit redirect
   to placement edit page Added error to goals if no questions answered Added Placement Google Map to
   Dashboard that shows all students from staffs courses


Ver 1.2.5 [2014-12-09]:
-------------------------------
 - Added GOALS view action to student view


Ver 1.2.4 [2014-12-04]:
-------------------------------
 - Small update


Ver 1.2.0 [2014-11-17]:
----------------
 - Finished updates
 - Main sectiosn of the GOALS question manager is completed.
 - Implemented sorting to question list
 - Started adding new GOALS question manager
 - Started updated to GOALS questions ordering
 - Changes after github migration
 - Fixed javascript files after removal of jquery in assets dir


Ver 1.0.5 [2013-12-14]:
----------------
 - Fixed email issues when running from a cron/bin file
 - Added option to dissable the auto-approve engine for courses
 - Added option to remove supervisor field for comapnies and placements
 - Clean up any var dumps no loner used
 - Tested placement system for new Ag students
 - Tag: Restore trunk composer.json
 - Tag: Updated changelog.md file for tag: 1.0.3
 - Tag: Updated composer.json for tag release
 - Updated Table to work with anon objects
 - Updated From js tkSubmitForm function
 - Tag: Updated changelog.md file for tag: 1.0.2
 - Updated DB toatal count query
 - Updating fonts
 - Added filters to GOALS plugin
 - Updated Import System
 - Added new Multi Select javascript
 - Fixec Table CSV Action to handle large data blocks
 - Added pending on historic placements
 - Fixed minor UI issues on Placement Manager and Mail log.
 - Fixed mail log pagenation.


Ver 1.0.4 [2013-12-14]:
----------------
 - Fixed email issues when running from a cron/bin file
 - Added option to dissable the auto-approve engine for courses
 - Added option to remove supervisor field for comapnies and placements
 - Clean up any var dumps no loner used
 - Tested placement system for new Ag students
 - Tag: Restore trunk composer.json
 - Tag: Updated changelog.md file for tag: 1.0.3
 - Tag: Updated composer.json for tag release
 - Updated Table to work with anon objects
 - Updated From js tkSubmitForm function
 - Tag: Updated changelog.md file for tag: 1.0.2
 - Updated DB toatal count query
 - Updating fonts
 - Added filters to GOALS plugin
 - Updated Import System
 - Added new Multi Select javascript
 - Fixec Table CSV Action to handle large data blocks
 - Added pending on historic placements
 - Fixed minor UI issues on Placement Manager and Mail log.
 - Fixed mail log pagenation.


Ver 1.0.3 [2013-12-14]:
----------------
 - Tag: Updated composer.json for tag release
 - Updated Table to work with anon objects
 - Updated From js tkSubmitForm function
 - Tag: Restore trunk composer.json
 - Tag: Updated changelog.md file for tag: 1.0.2
 - Updated DB toatal count query
 - Updating fonts
 - Added filters to GOALS plugin
 - Updated Import System
 - Added new Multi Select javascript
 - Fixec Table CSV Action to handle large data blocks
 - Added pending on historic placements\nFixed minor UI issues on Placement Manager and Mail
   log.\nFixed mail log pagenation.


Ver 1.0.2 [2013-12-14]:
----------------
 - Tag: Updated composer.json for tag release
 - Updated DB toatal count query
 - Updating fonts
 - Added filters to GOALS plugin
 - Updated Import System
 - Added new Multi Select javascript
 - Fixec Table CSV Action to handle large data blocks
 - Added pending on historic placements\nFixed minor UI issues on Placement Manager and Mail
   log.\nFixed mail log pagenation.


