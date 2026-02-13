module.exports = {
  content: [
    "./src/**/*.{php,js,html}",
    "./public/**/*.{php,js,html}",
    "./includes/**/*.{php,js,html}"
  ],
  plugins: [require("daisyui")],
  daisyui: {
    themes: ["light", "dark"],
  },
}
