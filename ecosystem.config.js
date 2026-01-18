module.exports = {
  apps: [{
    name: 'rhd-bi-report',
    script: 'artisan',
    args: 'serve --host=0.0.0.0 --port=9010',
    interpreter: 'php',
    cwd: '/home/rana-workspace/RHD-BI-Report',
    watch: false,
    max_memory_restart: '500M',
    env: {
      APP_ENV: 'production'
    }
  }]
};
