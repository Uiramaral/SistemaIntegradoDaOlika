export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        background: "#f8f6f2",
        sidebar: {
          DEFAULT: "#3b2b1f",
          accent: "#e37524",
          hover: "#f1a25a",
          border: "#4a3b31",
          foreground: "#ffffff",
        },
        card: "#ffffff",
        border: "#eae7e2",
        muted: "#8a837d",
        primary: "#c45a1e",
        "primary-hover": "#b14f17",
        "primary-soft": "#f2e5db",
        success: "#2b8a5b",
        warning: "#b58a00",
        destructive: "#d9534f",
      },
      fontFamily: {
        sans: ["Inter", "sans-serif"],
        display: ["Outfit", "sans-serif"],
      },
      borderRadius: {
        lg: "12px",
      },
      boxShadow: {
        card: "0 2px 6px rgba(0, 0, 0, 0.05)",
      },
    },
  },
  plugins: [],
};
