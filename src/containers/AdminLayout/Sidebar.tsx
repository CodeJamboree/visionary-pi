import Drawer from "@mui/material/Drawer";
import Box from "@mui/material/Box";
import List from "@mui/material/List";
import ListItemText from "@mui/material/ListItemText";
import ListItemIcon from "@mui/material/ListItemIcon";
import ListItemButton from "@mui/material/ListItemButton";
import CampaignIcon from '@mui/icons-material/Campaign';
import LiveTv from '@mui/icons-material/LiveTv';
import ImageSearchIcon from '@mui/icons-material/ImageSearch';
import { useNavigate } from 'react-router-dom';
import RemoveRedEyeIcon from '@mui/icons-material/RemoveRedEye';

const Sidebar = () => {
  const navigate = useNavigate();
  const handleClick = (path: string) => () => navigate(path);
  return (
    <Drawer open={true} variant="permanent" sx={{
      flexShrink: 0,
      [`& .MuiDrawer-paper`]: { width: 240, boxSizing: 'border-box' }
    }}>
      <Box sx={{ overflow: 'auto', marginTop: '94px' }}>
        <List>
          <ListItemButton onClick={handleClick("/admin/displays")}>
            <ListItemIcon>
              <LiveTv />
            </ListItemIcon>
            <ListItemText primary="Displays" />
          </ListItemButton>
          <ListItemButton onClick={handleClick("/admin/campaigns")}>
            <ListItemIcon>
              <CampaignIcon />
            </ListItemIcon>
            <ListItemText primary="Campaigns" />
          </ListItemButton>
          <ListItemButton onClick={handleClick("/admin/media")}>
            <ListItemIcon>
              <ImageSearchIcon />
            </ListItemIcon>
            <ListItemText primary="Media" />
          </ListItemButton>
          <ListItemButton onClick={handleClick("/")}>
            <ListItemIcon>
              <RemoveRedEyeIcon />
            </ListItemIcon>
            <ListItemText primary="Preview" />
          </ListItemButton>
        </List>
      </Box>
    </Drawer>
  );
}
export default Sidebar;
