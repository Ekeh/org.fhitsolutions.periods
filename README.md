# Periods

The CiviCRM Membership Periods extension for tracking membership period creation, editing and renewal process,
providing detailed overview of how long a contact has been a member. 

## Installation
After setting up your CiviCRM ([Installation Guide](https://docs.civicrm.org/sysadmin/en/latest/)), 
clone this repo into the extension directory and install. Please refer to the [extension installation
documentation](https://docs.civicrm.org/user/en/latest/introduction/extensions/#installing-extensions).

## Motivation
Currently, when a membership is renewed in CiviCRM the `end date` field on the membership
itself is extended by the length of the membership as defined in CiviCRM membership type configuration
but no record of the actual length of any one period or term is recorded. As such it is not possible
to see how many “terms” or “periods” of membership a contact may have had. 

This implies that if a membership commenced on `1 Jan 2014` and each term was of `12 months`
 in length, by `1 Jan 2016` the member would be renewing for their 3rd term. The terms would be:

|Term/Period | Start Date| End Date|
|---         | ---       | ---     |
|1 | 1 Jan 2014 | 31 Dec 2015 |
|2 | 1 Jan 2015 | 31 Dec 2016 |
|3 | 1 Jan 2016 | 31 Dec 2017 |

The aim of this extension is to extend the CiviCRM membership component so that when a 
membership is created or renewed a record for the membership `period` is recorded. 

The membership period is also connected to a contribution record if a payment is taken
 for membership or renewal.

## How to
1. After successfully installing the extension, click on membership menu in the navigation bar
and select dashboard.

    **NOTE:**
    >This is with the assumption that you already have contact record created. If not, kindly
    create new contact using the contact menu on navigation bar or
    [refer to user documentation](https://docs.civicrm.org/user/en/latest/common-workflows/importing-data-into-civicrm)

2. Click on `name` of a contact from the recent membership page list. 
Alternatively, you can click on the `view` link at the end of each row. 

3. From the list of tabs provided, click on `Membership` tab to create, edit or renew
contact membership. If this is successful, membership period will be automatically
updated.

4. Click on the `Membership Periods` tab to view history of times/periods and their
contributions (if applicable).
    > Note that page refresh might be required to see changes, especially after membership
    creation
5. To view membership contribution breakdown, click on any of the contribution link

## API
The following are usage of some of the API interaction endpoints available for the 
Membership Period extension.

#### Get Membership Periods

```text
REST:

http://dev.local.civicrm/sites/all/modules/civicrm/extern/rest.php?entity=Periods&action=get&api_key=userkey&key=sitekey&json={"sequential":1}
```

```php
PHP: 

$result = civicrm_api3('Periods', 'get', array(
  'sequential' => 1,
));
```
#### Create or Update Membership Period
```text
REST:

http://dev.local.civicrm/sites/all/modules/civicrm/extern/rest.php?entity=Periods&action=create&api_key=userkey&key=sitekey&json={"sequential":1,"start_date":"2017-01-01","end_date":"2019-12-01","membership_id":22}
```

```php
PHP

$result = civicrm_api3('Periods', 'create', array(
  'sequential' => 1,
  'start_date' => "2017-01-01",
  'end_date' => "2019-12-01",
  'membership_id' => 22,
));
```

For full details of other options, refer to [the CiviCRM API](https://docs.civicrm.org/dev/en/latest/api/)

## Warnings
* It should be noted that periods are automatically generated when  membership 
events are raised for create and renewal. There is no need of calling the create api endpoint directly.

* It is assumed that changes to membership end date equivalent to the next membership 
duration date, with reference to the last period end date is a renewal.

* It is assumed that the last membership period created is the active period. No reference is made to previous periods
except for determining next renewal date.

## License
Copyright © 2017 [Alajede Samson](https://github.com/ajesamson). 
Licensed under the [GNU Affero Public License 3.0](https://github.com/ajesamson/org.fhitsolutions.periods/blob/master/LICENSE.txt)

