Stable API + Tester bundle (2025-08-11)

1) Unzip into project root.
2) In app/Config/Filters.php add alias:
   'stableapiauth' => \App\Filters\StableApiAuthFilter::class,
3) Add routes from ROUTES_TO_ADD.txt to app/Config/Routes.php (outside adminauth).
4) Set token in .env:
   ANDROID_API_TOKEN="your-long-random-token"
5) Open tester:
   /index.php/tools/stable-api-tester?key=arvindrchauhan1723
