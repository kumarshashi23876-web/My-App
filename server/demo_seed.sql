-- Optional demo data. Change/remove URLs before production if desired.

INSERT INTO links
(category_id, title, description, url, logo_url, open_mode, is_featured, sort_order, is_active)
SELECT c.id, 'Aaj Tak', 'Hindi news', 'https://www.aajtak.in/', NULL, 'in_app', 1, 10, 1
FROM categories c
WHERE c.name='News'
AND NOT EXISTS (SELECT 1 FROM links WHERE title='Aaj Tak');

INSERT INTO links
(category_id, title, description, url, logo_url, open_mode, is_featured, sort_order, is_active)
SELECT c.id, 'Zee News', 'Hindi news', 'https://zeenews.india.com/', NULL, 'in_app', 0, 20, 1
FROM categories c
WHERE c.name='News'
AND NOT EXISTS (SELECT 1 FROM links WHERE title='Zee News');

INSERT INTO links
(category_id, title, description, url, logo_url, open_mode, is_featured, sort_order, is_active)
SELECT c.id, 'Facebook', 'Open Facebook', 'https://www.facebook.com/', NULL, 'external', 0, 10, 1
FROM categories c
WHERE c.name='Social'
AND NOT EXISTS (SELECT 1 FROM links WHERE title='Facebook');

INSERT INTO links
(category_id, title, description, url, logo_url, open_mode, is_featured, sort_order, is_active)
SELECT c.id, 'Instagram', 'Open Instagram', 'https://www.instagram.com/', NULL, 'external', 0, 20, 1
FROM categories c
WHERE c.name='Social'
AND NOT EXISTS (SELECT 1 FROM links WHERE title='Instagram');

INSERT INTO links
(category_id, title, description, url, logo_url, open_mode, is_featured, sort_order, is_active)
SELECT c.id, 'X', 'Open X', 'https://x.com/', NULL, 'external', 0, 30, 1
FROM categories c
WHERE c.name='Social'
AND NOT EXISTS (SELECT 1 FROM links WHERE title='X');
