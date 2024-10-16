# TODO

[x] Finsh image upload log form and convert to modal
[x] Allow image preview delete and modal clear
[x] Improve upload bar
[x] Finish log list grid and display
[x] Make airports list dynamic
[x] Create individual log modal/page
[x] Edit log
[x] Finish log view
[x] Delete log
[x] Images on s3
[x] Lazy load images as not to spam s3
[x] Image safety checks
[x] Error reporting (Look at sentry)
[x] Analytics (Look at posthog)
[x] Implement the toast lib for all action confirmations
[x] Home page and pricing page
[x] Registration page and implement stripe or other payment provider
[x] Can see other users logs
[x] Change log addition to not add multiple logs per image
[x] Chnage log view to show images view
[x] WHen clikcing on an image, show the log image carrousel starting with the selected image
[x] A page to view logs and a page to view all images - image page will show fullscreen image when clicked
[x] efficiently load images form AWS with pagination for images page and logs page
[x] add video uploads
[x] Handle video compression
[x] Handle HEIC and other non jpeg or PNG upload types
[ ] Better log saveing process to prevent timeouts and informt he users the log is being created
[ ] Make sure temp files are removed once persistsed to s3
[ ] Add departure/destination airport, and also flight number
[ ] Email verification on signup
[ ] Send to facebook
[ ] User area upgrades for subscription view and cancel
[ ] Add avatar to nav bar
[ ] Auth themeing
[ ] S3 look at cors to specifiyt he correct domain
[ ] Perfection pass
[ ] Beuutify sales page
[ ] Subscription emails and verficiation and trial end prompts and actual
[ ] trial days left banner (colapsable)
[ ] register business
[ ] Business bank account
[ ] Update stripe account


## Later

[ ] Show avatar on log
[ ] Like other people logs
[ ] Google and facebook auth (clerk maybe?)
[ ] Comment on other peoples logs
[ ] Host videos and safety
[ ] share to social media with a watermark
[ ] Allow logs to be made public
[ ] Show all public logs
[ ] Comment on public logs
[ ] Scale down image safety checks as the user proves trustworthy. e,g, 100% for first month, reduced to a % probbaility that   decreases over time, probably to a minumum of 10% of uploads checked?


## Local env
- PHP
- postgres
- node
- npm
- nginx
- stripe cli (https://www.youtube.com/watch?v=2_BsWO5WRmU)

sudo apt update
sudo apt install ffmpeg (compression)

sudo apt install imagemagick (image conversion, specifically heic to jpeg)

