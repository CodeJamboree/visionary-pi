import AppBar from "@mui/material/AppBar";
import Typography from "@mui/material/Typography";
import Link from "@mui/material/Link";
import Container from "@mui/material/Container";
import React from "react";

const Header: React.FC = () => {

  return (
    <AppBar
      position="fixed"
      color="secondary"
      sx={{
        top: 'auto',
        bottom: 0,
        zIndex: (theme) => theme.zIndex.drawer + 1
      }}
    >
      <Container sx={{ display: "flex", textAlign: 'right' }}>
        <Typography variant="body2" sx={{ flexGrow: 1 }}>
          <Link href="https://github.com/CodeJamboree/visionary-pi">Open-Source</Link>
          &nbsp;software
          Created by <Link href="https://codejamboree.com">Code Jamboree, LLC</Link>
        </Typography>
      </Container>
    </AppBar>
  );
};
export default Header;