import Typography from "@mui/material/Typography";
import Grid from "@mui/material/Grid2";
import Paper from "@mui/material/Paper";

const Home = () => {
  return (
    <>

      <Grid
        container
        spacing={3}
        direction="column"
        alignItems="center"
        justifyContent="center"
        style={{ minHeight: "80vh" }}
      >
        <Grid size={3}>
          <Typography variant="h4" color="secondary">
            Home
          </Typography>
        </Grid>
        <Grid>
          <Paper variant="outlined">
            <Typography variant="body2" color="inherit">
              Welcome to the Visionary Pi Digital Signage manager
            </Typography>
          </Paper>
        </Grid>
      </Grid>
    </>
  );
};

export default Home;