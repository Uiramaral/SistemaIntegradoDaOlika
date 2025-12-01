export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        background: "#faf8f5",
        sidebar: {
          DEFAULT: "#3b2f26",
          accent: "#e86b00",
          border: "#4a3b31",
          foreground: "#ffffff",
        },
        card: "#ffffff",
        border: "#e5ded8",
        muted: "#9c938c",
        primary: "#e86b00",
        success: "#4caf50",
        warning: "#d48d00",
        destructive: "#d9534f",
      },
      fontFamily: {
        sans: ["Inter", "sans-serif"],
        display: ["Outfit", "sans-serif"],
      },
      borderRadius: {
        lg: "10px",
      },
      boxShadow: {
        soft: "0 2px 4px rgba(0,0,0,0.05)",
      },
    },
  },
  plugins: [],
};

