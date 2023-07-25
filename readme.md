## Installation

- PHP
- MySQL
  The docker file attached here needs testing. I couldn't do that due to short of time.

## Setup

1. Update `.env` file with proper credentials
2. Call ping API to test
3. Place all data inside `data/input_data.jsonl`
4. Load makers and brands
5. Call split products API. Now each file will have 10,000 records. This is done to decrease loading time.
6. Load product with `file_number` parameter.
7. Now you can search or get reports accordingly

### Following issues are present:

- Duplicate brands will cause errors
- Data Loss due to strlower
- We can track which splitted file loaded in furture to avoid duplicate
