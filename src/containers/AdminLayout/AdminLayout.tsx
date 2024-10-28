import { FC, useMemo } from "react";
import useMediaQuery from "@mui/material/useMediaQuery";
import { ThemeProvider } from "@mui/material/styles";

import CssBaseline from "@mui/material/CssBaseline";
import Container from "@mui/material/Container";
import Box from "@mui/material/Box";
import Header from "./Header";
import Footer from "./Footer";
import Sidebar from './Sidebar';
import buildTheme from "./buildTheme";
import { Outlet } from 'react-router-dom';


const AdminLayout: FC = () => {

  const prefersDarkColorScheme = useMediaQuery("(prefers-color-scheme: dark)");
  const theme = useMemo(() => buildTheme(prefersDarkColorScheme), [prefersDarkColorScheme]);

  return (
    <ThemeProvider theme={theme}>
      <Box sx={{ display: 'flex', marginTop: '94px' }}>
        <CssBaseline />
        <Header />
        <Sidebar />
        <Container sx={{ marginLeft: '240px', marginBottom: '32px' }}>
          <Outlet />
        </Container>
        <Footer />
      </Box>
    </ThemeProvider>
  );
};

export default AdminLayout;