# My App v6 API Summary

Base path example:

`https://yourdomain.com/myapp/api/`

## Public
- `home.php`
- `ads.php?slot=home_inline`
- `ad_click.php?id=123`
- `notifications.php` (also supports logged-in read state)
- `click.php?id=123`
- `device_register.php`
- `activity.php`

## Authentication
- `register.php`
- `login.php`
- `me.php`
- `logout.php`

Authenticated endpoints use:

`Authorization: Bearer <token>`

## Favorites
### GET `favorites.php`
Returns current user's saved links.

### POST `favorites.php`
```json
{
  "link_id": 123,
  "action": "add"
}
```

Use `"remove"` to delete.

## Notifications
### GET `notifications.php`
When logged in, response includes:
- `is_read`
- `unread_count`

### POST `notification_read.php`
```json
{
  "notification_id": 10
}
```

### POST `notifications_read_all.php`
Marks current live notifications as read.

## Device Registration
POST `device_register.php`

```json
{
  "device_id": "stable-local-device-id",
  "push_token": "future-fcm-token"
}
```

This API is ready for Firebase Cloud Messaging integration later.

## Activity
POST `activity.php`

```json
{
  "event_type": "link_open",
  "reference_id": 123
}
```

Allowed events:
- app_open
- link_open
- favorite_add
- favorite_remove
- notification_open
- ad_open
- login
- logout
