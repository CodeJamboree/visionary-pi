import AppBar from "@mui/material/AppBar";
import Toolbar from "@mui/material/Toolbar";
import Container from "@mui/material/Container";
import Typography from '@mui/material/Typography';
import IconButton from '@mui/material/IconButton';

const Header: React.FC = () => {
  return (
    <AppBar position="fixed" color="primary" sx={{
      zIndex: (theme) => theme.zIndex.drawer + 1
    }}>
      <Container maxWidth="xl">
        <Toolbar disableGutters>
          <IconButton size="large" edge="start" color="inherit" aria-label="menu" sx={{ mr: 2 }}>
            <img src="/logo/logo_64.png" />
          </IconButton>
          <Typography variant="h6" component="div" sx={{ flexGrou: 1 }}>
            Visionary Pi Digital Signage
          </Typography>
        </Toolbar>
      </Container>
    </AppBar>
  );
};

export default Header;