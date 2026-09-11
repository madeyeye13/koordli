Commit and push to GitHub once it works locally:

powershell
git add .
git commit -m "describe the change"
git push origin main

4. Pull on the VPS and rebuild:

bash
cd ~/koordli/app
git pull origin main
cd ~/koordli
docker compose build app queue scheduler reverb
docker compose up -d
docker compose restart nginx