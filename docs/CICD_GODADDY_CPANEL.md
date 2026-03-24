# CI/CD Setup for GoDaddy cPanel

This project deploys through GitHub Actions using FTP to cPanel hosting.

## 1. Prerequisites

- A GitHub repository for this project
- GoDaddy cPanel FTP account enabled
- Your site document root path in cPanel (for example: `/public_html/`)

## 2. Required GitHub Secrets

In GitHub: `Settings -> Secrets and variables -> Actions -> New repository secret`

Create these secrets:

- `CPANEL_FTP_SERVER`: Your FTP host (example: `ftp.yourdomain.com`)
- `CPANEL_FTP_USERNAME`: FTP username
- `CPANEL_FTP_PASSWORD`: FTP password
- `CPANEL_TARGET_DIR`: Target folder in hosting (example: `/public_html/`)

## 3. Workflow Behavior

Workflow file: `.github/workflows/deploy-cpanel.yml`

- Triggered on push to `main` or `master`
- Also supports manual run via `workflow_dispatch`
- Runs PHP syntax lint (`php -l`) before deployment
- Deploys with `SamKirkland/FTP-Deploy-Action`
- Uses `ftps` on port `21`
- Does not wipe the remote directory (`dangerous-clean-slate: false`)

## 4. First Deployment Check

After pushing to `main` or `master`:

- Open `Actions` tab in GitHub
- Check `CI/CD to GoDaddy cPanel` run
- Confirm `PHP Syntax Check` and `Deploy to GoDaddy cPanel` both succeed

## 5. Common GoDaddy Notes

- If deploy fails with auth errors, re-check FTP username/password and host
- If deploy uploads to wrong place, fix `CPANEL_TARGET_DIR`
- If FTPS is blocked in hosting config, switch workflow `protocol` to `ftp`
