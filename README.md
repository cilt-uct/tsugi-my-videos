# Tsugi: My videos
A page to introduce My Videos to Amathuba sites and help with DIY recordings.

If the user does not have an existing Personal Series in Opencast, then it shows the benefits of creating one, the terms of use, and a button to do so.

If the user already has a Personal Series then the Opencast "My Videos" LTI page is launched.

### Development

Copy over `tool-config_dist.php` to `tool-config.php` and update the values.

| Value | Description |
|-------|-------------|
|`debug`| Enable debugging for the tool |
|`active`| Is the tool active or should the "Coming soon" page be displayed instead |
|`notification_list`| Delimeted list of administrator emails that will receive notifications from this tool |
|`middleware_opencasturl`| URL to the middleware which helps to lookup personal series and create one |
|`middleware_username`| Username to access middleware |
|`middleware_password`| Password to access middleware |
|`opencast_ltiurl`| Opencast LTI launch URL |
|`opencast_ltikey`| Opencast LTI launch Key |
|`opencast_ltisecret`| Opencast LTI launch Secret |
