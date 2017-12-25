
News-2: Add new features to existing module yii2module-news_1b_160430
=====================================================================

Demonstration of modules inheritance:
- configs and params will merge with ancestor's data
- messages will merge
- for route(s) get latest file(s)
- for view(s) get latest file(s)
- controllers and models - traditional inheritance

Basic (ancestor) module-package is news_1b_160430.

New features in version news_2b_161124:
- add slug of news
- url for news http://.../en/news/view/link-with-slug
  instead of http://.../en/news/view/NNN
- news archivation
- add date-time correction according to time zone
