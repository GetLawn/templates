const { existsSync, readFileSync, writeFileSync } = require("node:fs");

const configPath = "/root/.affine/config/config.json";
const config = existsSync(configPath)
  ? JSON.parse(readFileSync(configPath, "utf8"))
  : {};

if (config.flags?.allowGuestDemoWorkspace === undefined) {
  config.flags = { ...config.flags, allowGuestDemoWorkspace: false };
  writeFileSync(configPath, JSON.stringify(config, null, 2) + "\n");
}
