import { FC, useState } from "react";
import { MediaListItem, MediaListParams, useListQuery } from "./mediaApi";
import Grid from '@mui/material/Grid2';
import Card from '@mui/material/Card';
import CardHeader from '@mui/material/CardHeader';
import CardMedia from '@mui/material/CardMedia';
import CardContent from '@mui/material/CardContent';
import CardActions from '@mui/material/CardActions';
import Typography from '@mui/material/Typography';
import IconButton from '@mui/material/IconButton';
import MoreVertIcon from '@mui/icons-material/MoreVert';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';

const MediaList: FC = () => {
  const [params, _setParams] = useState<MediaListParams>({})
  const {
    data,
    // isError,
    // error,
    // isLoading
  } = useListQuery(params);

  return <Grid container spacing={2}>
    {(data?.rows ?? []).map((item) => <ImageItem key={item.id}
      item={item}
    />)}
  </Grid>;
}

const msAsDuration = (totalMs: number) => {
  if ((totalMs ?? 0) === 0) return 'n/a';
  const ms = totalMs % 1000;
  totalMs -= ms;
  totalMs /= 1000;
  const s = totalMs % 60;
  totalMs -= s;
  totalMs /= 60;
  const min = totalMs % 60;
  totalMs -= min;
  totalMs /= 60;
  const hour = totalMs % 24;
  totalMs -= hour;
  totalMs /= 25;
  const day = totalMs;

  const parts = [day, hour, min, s];
  while (parts.length !== 2 && parts[0] === 0) {
    parts.shift();
  }
  let duration = parts.map(part => part.toString().padStart(2, '0')).join(':');
  if (ms > 0) duration += `.${ms.toString().padStart(3, '0')}`;
  return duration;

}

const ImageItem: FC<{
  item: MediaListItem
}> = ({ item: {
  width,
  height,
  displayName,
  url,
  createdAt,
  duration,
  videoFormat,
  audioFormat,
  fileFormat
} }) => {
    let created = new Date(createdAt * 1000);
    let iWidth = width ?? 164;
    let iHeight = height ?? 164;
    if (iWidth > 164) {
      iHeight *= 164 / iWidth;
      iWidth = 164;
    }
    if (iHeight > 164) {
      iWidth *= 164 / iHeight;
      iHeight = 164;
    }

    return <Grid size={3}>
      <Card>
        <CardHeader
          action={
            <IconButton aria-label="settings">
              <MoreVertIcon />
            </IconButton>
          }
          title={displayName}
          subheader={created.toLocaleDateString()}
        />
        <CardMedia
          component="img"
          height={iHeight}
          image={url}
          alt={displayName}
        />
        <CardContent>
          {videoFormat ?? audioFormat ?? fileFormat}
          <Typography variant="body2" sx={{ color: 'text.secondary' }}>
            {msAsDuration(duration)}
          </Typography>
        </CardContent>
        <CardActions disableSpacing>
          <IconButton aria-label="edit">
            <EditIcon />
          </IconButton>
          <IconButton aria-label="share">
            <DeleteIcon />
          </IconButton>

          <ExpandMoreIcon />
        </CardActions>
      </Card>
    </Grid>
  }
export default MediaList;
