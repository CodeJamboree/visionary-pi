import { createTheme } from "@mui/material/styles";

const primaryFontName = "Avenir";
const raspberryColor = "#ff0074";
const raspberryShadow = "#9f003f";
const leafColor = "#ff0074";
const leafShadow = "#256776";
const displayColor = "#538ab9";
const displayShadow = "#244f6e";
const black = "#000000";
const white = "#ffffff";


const lightPalette = {
  primary: {
    main: raspberryColor,
    dark: raspberryShadow,
  },
  secondary: {
    main: leafColor,
    dark: leafShadow,
  },
  tertiary: {
    main: displayColor,
    dark: displayShadow
  },
  text: {
    primary: black,
    secondary: white
  },
  background: {
    default: '#f0f0f0',
    paper: white,
  },
  contrastThreshold: 3,
  tonalOffset: 0.2,
};
const darkPalette = {
  primary: {
    main: raspberryColor,
    dark: raspberryShadow,
  },
  secondary: {
    main: leafColor,
    dark: leafShadow,
  },
  tertiary: {
    main: displayColor,
    dark: displayShadow
  },
  text: {
    primary: white,
    secondary: displayColor
  },
  background: {
    default: black,
    paper: '#252c31',
  },
  contrastThreshold: 3,
  tonalOffset: 0.2,
};
// Avenir is the font used within our branding
const fontFamily = [
  primaryFontName,
  "Montserrat", // Similar to Avenir
  "Roboto",
  "Helvetica",
  "Arial",
  "sans-serif",
]
  .map((name) => `"${name}"`)
  .join(",");

const buildTheme = (prefersDarkColorScheme: boolean) =>
  createTheme({
    typography: {
      fontFamily,
    },
    palette: {
      mode: prefersDarkColorScheme ? "dark" : "light",
      ...(prefersDarkColorScheme ? darkPalette : lightPalette),
    },
  });

export default buildTheme;